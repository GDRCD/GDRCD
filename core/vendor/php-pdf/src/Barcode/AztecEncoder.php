<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * Aztec 2D barcode (ISO/IEC 24778:2008).
 *
 * Implementation scope:
 *  - Compact (sizes 15×15 .. 27×27, layers 1-4) and Full Aztec
 *    (15×15..151×151, layers 5-32).
 *  - Full ASCII via 5 character modes (Upper/Lower/Mixed/Punct/Digit)
 *    with mode latches/shifts.
 *  - Binary mode (B/S) for bytes > 127 or non-encodable chars.
 *  - Bit stuffing per ISO §6.4.4 (no all-zero/all-one codewords).
 *  - Reed-Solomon ECC over GF(16) for mode message, GF(64) for 1-2L
 *    data, GF(256) for 3-4L data.
 *  - Auto layer selection with ~23% ECC redundancy (per ISO recommendation).
 *
 * Not implemented:
 *  - Rune mode (single-character symbol) — v1.3 backlog.
 *  - Structured Append (multi-symbol concatenation) — v1.3 backlog.
 *  - ECI / FLG(n) extended channel interpretation — v1.3 backlog.
 *
 * Output via {@see modules()} — 2D bool matrix; true = black module.
 */
final class AztecEncoder
{
    // ---------------- Character mode tables ----------------

    /** Special action codes (returned from encode functions as negative ints). */

    /** Mode IDs. */
    private const MODE_UPPER = 0;
    private const MODE_LOWER = 1;
    private const MODE_MIXED = 2;
    private const MODE_PUNCT = 3;
    private const MODE_DIGIT = 4;

    /** Bit widths per mode. */
    private const MODE_BITS = [
        self::MODE_UPPER => 5,
        self::MODE_LOWER => 5,
        self::MODE_MIXED => 5,
        self::MODE_PUNCT => 5,
        self::MODE_DIGIT => 4,
    ];

    // Character → (mode, code-value) lookup. Built lazily in classmap().
    /** @var array<int, list<array{0:int, 1:int}>>|null */
    private static ?array $charMap = null;

    // ---------------- Size / GF info ----------------

    /**
     * Word size (codeword bit width) by layer count (index 1..32).
     * Index 0 reserved for mode message (always 4 bits, GF(16)).
     */
    private const WORD_SIZE = [
        4,
        6, 6, 8, 8, 8, 8, 8, 8,
        10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10,
        12, 12, 12, 12, 12, 12, 12, 12, 12, 12,
    ];

    /** Primitive polynomial for each word size, GF(2^n). */
    private const PRIM_POLY = [
        4 => 0x13,    // x^4 + x + 1 (Aztec param, GF(16))
        6 => 0x43,    // x^6 + x + 1 (GF(64))
        8 => 0x12D,   // x^8 + x^5 + x^3 + x^2 + 1 (Aztec data 8, GF(256))
        10 => 0x409,  // x^10 + x^3 + 1 (Aztec data 10, GF(1024))
        12 => 0x1069, // x^12 + x^6 + x^5 + x^3 + 1 (Aztec data 12, GF(4096))
    ];

    public readonly bool $compact;

    public readonly int $layers;

    public readonly int $size;

    /** Base matrix size before addition of alignment lines (Full mode). */
    public readonly int $baseMatrixSize;

    public readonly int $dataCodewords;

    public readonly int $eccCodewords;

    /** @var list<list<bool>> */
    private array $matrix;

    /**
     * Aztec ECI marker via FLG(n) escape (ISO 24778 §6.5).
     *
     * Inserts ECI escape at start of encoded data stream signaling to the
     * decoder that following content uses non-default character encoding
     * (e.g., ECI 26 = UTF-8, ECI 25 = UCS-2 Big-Endian).
     *
     * Bit sequence (from default Upper start mode):
     *  - U→M latch (5 bits = 11101)
     *  - M→P latch (5 bits = 11110)
     *  - FLG codeword 0 in Punct (5 bits = 00000)
     *  - 3-bit n value (length of decimal ECI value, 1-6)
     *  - n × 4-bit digit codewords (codes 2-11 for '0'-'9')
     *  - P→U latch (5 bits = 11111) — return to Upper for data encoding
     *
     * @param  int  $eciValue  ECI value 0..999999 (max 6 decimal digits)
     */
    public static function withEci(int $eciValue, string $data, int $minEccPercent = 23): self
    {
        if ($eciValue < 0 || $eciValue > 999999) {
            throw new \InvalidArgumentException('Aztec ECI value must be 0..999999');
        }

        return new self($data, $minEccPercent, eciValue: $eciValue);
    }

    /**
     * Build FLG(n) ECI header bits — see withEci() docblock for sequence spec.
     */
    private static function buildEciHeader(int $eciValue): string
    {
        $eciStr = (string) $eciValue;
        $n = strlen($eciStr);

        $bits = '';
        // U→M latch (Upper code 29 = 5 bits).
        $bits .= str_pad(decbin(29), 5, '0', STR_PAD_LEFT);
        // M→P latch (Mixed code 30 = 5 bits).
        $bits .= str_pad(decbin(30), 5, '0', STR_PAD_LEFT);
        // FLG codeword 0 (Punct code 0 = 5 bits).
        $bits .= str_pad(decbin(0), 5, '0', STR_PAD_LEFT);
        // 3-bit n value (length of ECI digits, 1-6).
        $bits .= str_pad(decbin($n), 3, '0', STR_PAD_LEFT);
        // n × 4-bit digit codewords (digit '0'-'9' = code 2-11 in Digit mode).
        for ($i = 0; $i < $n; $i++) {
            $digit = (int) $eciStr[$i];
            $bits .= str_pad(decbin(2 + $digit), 4, '0', STR_PAD_LEFT);
        }
        // P→U latch (Punct code 31 = 5 bits) — return to Upper for data encoding.
        $bits .= str_pad(decbin(31), 5, '0', STR_PAD_LEFT);

        return $bits;
    }

    /**
     * Aztec Structured Append (ISO 24778 §8.4).
     *
     * Builds multi-symbol concatenated set. Header prefixed to data:
     *   " {idx_letter}{cnt_letter}{data}"
     * or with fileID:
     *   "{fileID} {idx_letter}{cnt_letter}{data}"
     *
     * Decoder concatenates data from all symbols in position order, recognizes
     * structured append via leading-space-or-fileID-space prefix.
     *
     * @param  string  $data  Data segment for this symbol.
     * @param  int  $position  1-based position in set (1..26).
     * @param  int  $total  Total symbols in set (1..26).
     * @param  string|null  $fileId  Optional alphanumeric ID (max 24 chars,
     *                                shared across all symbols in set).
     */
    public static function structuredAppend(
        string $data,
        int $position,
        int $total,
        ?string $fileId = null,
        int $minEccPercent = 23,
    ): self {
        if ($position < 1 || $position > 26) {
            throw new \InvalidArgumentException('Aztec Structured Append position must be 1..26');
        }
        if ($total < 1 || $total > 26) {
            throw new \InvalidArgumentException('Aztec Structured Append total must be 1..26');
        }
        if ($position > $total) {
            throw new \InvalidArgumentException("Position $position cannot exceed total $total");
        }
        if ($fileId !== null) {
            if (strlen($fileId) > 24) {
                throw new \InvalidArgumentException('Aztec Structured Append fileID max 24 chars');
            }
            if (! preg_match('/^[A-Z0-9]+$/', $fileId)) {
                throw new \InvalidArgumentException('Aztec Structured Append fileID must be uppercase alphanumeric');
            }
        }

        // Format header.
        $idxLetter = chr(ord('A') + $position - 1);
        $cntLetter = chr(ord('A') + $total - 1);
        $header = ($fileId !== null ? $fileId : '').' '.$idxLetter.$cntLetter;

        return new self($header.$data, $minEccPercent);
    }

    /**
     * ECI prefix bits emitted via FLG(n) escape (ISO 24778 §6.5).
     * Null = no ECI. Set via `withEci()` static factory.
     */
    public readonly ?int $eciValue;

    public function __construct(
        public readonly string $data,
        int $minEccPercent = 23,
        ?int $eciValue = null,
    ) {
        if ($data === '') {
            throw new \InvalidArgumentException('Aztec input must be non-empty');
        }
        $this->eciValue = $eciValue;

        // 1. High-level encode → bit string.
        $bits = self::encodeToBits($data);
        // Prepend FLG(n) ECI header if set.
        if ($eciValue !== null) {
            $bits = self::buildEciHeader($eciValue).$bits;
        }

        // 2. Pick smallest size accommodating data + ECC.
        //    Iteration order per ZXing: Compact L=1..4, then Full L=4..32.
        //    Skip Full L=1..3 since Compact L=2..4 have same matrix sizes
        //    but more data capacity.
        $eccBits = intdiv(strlen($bits) * $minEccPercent, 100) + 11;
        $totalSizeBits = strlen($bits) + $eccBits;

        $chosenLayers = null;
        $chosenCompact = false;
        $wordSize = 0;
        $totalBitsInLayer = 0;
        $stuffedBits = '';
        for ($i = 0; $i <= 32; $i++) {
            $compact = $i <= 3;
            $L = $compact ? $i + 1 : $i;
            // totalBitsInLayer = ((88 if compact else 112) + 16L) * L
            $totalBitsInLayerForL = (($compact ? 88 : 112) + 16 * $L) * $L;
            if ($totalSizeBits > $totalBitsInLayerForL) {
                continue;
            }
            $ws = self::WORD_SIZE[$L];
            if ($wordSize !== $ws) {
                $wordSize = $ws;
                $stuffedBits = self::stuffBits($bits, $ws);
            }
            $usable = $totalBitsInLayerForL - ($totalBitsInLayerForL % $ws);
            // Compact limited to 64 message words.
            if ($compact && strlen($stuffedBits) > $ws * 64) {
                continue;
            }
            if (strlen($stuffedBits) + $eccBits <= $usable) {
                $chosenLayers = $L;
                $chosenCompact = $compact;
                $totalBitsInLayer = $totalBitsInLayerForL;
                break;
            }
        }
        if ($chosenLayers === null) {
            throw new \InvalidArgumentException('Aztec data too large (>32 Full layers)');
        }

        $this->compact = $chosenCompact;
        $this->layers = $chosenLayers;
        $this->baseMatrixSize = ($chosenCompact ? 11 : 14) + 4 * $chosenLayers;
        if ($chosenCompact) {
            $this->size = $this->baseMatrixSize;
        } else {
            // Full: insert 1 center alignment line + 2 lines per 15-step from center.
            $this->size = $this->baseMatrixSize + 1 + 2 * intdiv(intdiv($this->baseMatrixSize, 2) - 1, 15);
        }

        // 3. Generate check (ECC) words filling totalBitsInLayer.
        $messageBits = self::generateCheckWords($stuffedBits, $totalBitsInLayer, $wordSize);

        // 4. Generate mode message.
        $messageSizeInWords = intdiv(strlen($stuffedBits), $wordSize);
        $this->dataCodewords = $messageSizeInWords;
        $this->eccCodewords = intdiv($totalBitsInLayer, $wordSize) - $messageSizeInWords;
        $modeMessage = self::generateModeMessage($chosenCompact, $chosenLayers, $messageSizeInWords);

        // 5. Build matrix per ZXing algorithm.
        $this->matrix = $this->buildMatrix($messageBits, $modeMessage);
    }

    /** @return list<list<bool>> */
    public function modules(): array
    {
        return $this->matrix;
    }

    public function matrixSize(): int
    {
        return $this->size;
    }

    // ---------------- Character encoding ----------------

    /**
     * Convert string to Aztec bit stream switching modes optimally
     * (greedy heuristic, not optimal but correct).
     */
    public static function encodeToBits(string $text): string
    {
        self::classmap();
        $bits = '';
        $mode = self::MODE_UPPER;
        $len = strlen($text);

        for ($i = 0; $i < $len; $i++) {
            $ch = $text[$i];
            $ord = ord($ch);
            $entries = self::$charMap[$ord] ?? null;

            if ($entries === null) {
                // Non-encodable char — emit as binary shift.
                $bits .= self::emitBinaryShift($mode, [$ord]);

                continue;
            }

            // Pick best entry for current mode.
            $bestMode = null;
            $bestCode = null;
            $bestCost = PHP_INT_MAX;
            foreach ($entries as [$m, $c]) {
                $cost = self::switchCost($mode, $m);
                if ($cost < $bestCost) {
                    $bestCost = $cost;
                    $bestMode = $m;
                    $bestCode = $c;
                }
            }

            // Emit mode switch (latch or shift) if required.
            $bits .= self::switchModeBits($mode, $bestMode);
            // Update mode (latches change persistent mode; shifts handled inline).
            if ($bestMode !== self::MODE_PUNCT || $mode === self::MODE_MIXED) {
                // Latching transitions update mode; punct from non-mixed is a shift.
                if (self::isLatchSwitch($mode, $bestMode)) {
                    $mode = $bestMode;
                }
            } elseif ($mode === self::MODE_DIGIT && $bestMode === self::MODE_PUNCT) {
                // From Digit, going to Punct uses upper-latch then punct-shift — mode resets to Upper.
                $mode = self::MODE_UPPER;
            }
            // Emit char code in best mode.
            $bits .= str_pad(decbin($bestCode), self::MODE_BITS[$bestMode], '0', STR_PAD_LEFT);
        }

        return $bits;
    }

    /**
     * Cost of switching from $from to $to (number of bits for switch sequence).
     */
    private static function switchCost(int $from, int $to): int
    {
        if ($from === $to) {
            return 0;
        }
        // P/S (punct shift) from Upper/Lower/Mixed/Digit = 5 bits (or 4 for Digit).
        if ($to === self::MODE_PUNCT) {
            return self::MODE_BITS[$from]; // single shift cost.
        }
        // Most other transitions = 1 latch (5 bits or 4 for Digit).
        return self::MODE_BITS[$from];
    }

    private static function isLatchSwitch(int $from, int $to): bool
    {
        // Punct from non-Mixed = shift (returns to prior mode).
        // From Mixed → Punct = latch.
        if ($to === self::MODE_PUNCT && $from !== self::MODE_MIXED) {
            return false;
        }
        // From Lower, code 28 is U/S — an UPPER SHIFT valid for ONE
        // character; there is no direct Lower→Upper latch in Aztec.
        // Treating it as a latch desynchronized encoder state from the
        // decoder and inverted letter case until the next real latch.
        if ($from === self::MODE_LOWER && $to === self::MODE_UPPER) {
            return false;
        }

        return true;
    }

    /**
     * Emit mode-switch bits between $from and $to. Returns "" if same mode.
     */
    private static function switchModeBits(int $from, int $to): string
    {
        if ($from === $to) {
            return '';
        }
        $fromBits = self::MODE_BITS[$from];

        // Punct from non-Mixed: P/S shift (code 0 in all modes 5-bit; code 0 in Digit 4-bit).
        if ($to === self::MODE_PUNCT && $from !== self::MODE_MIXED) {
            return str_pad(decbin(0), $fromBits, '0', STR_PAD_LEFT);
        }

        // U/S from Lower (shift, one char only):
        // (Not used in greedy impl currently; latches preferred.)

        // Latch codes:
        // From Upper:  L/L=28, M/L=29, D/L=30
        // From Lower:  U/S=28(shift), M/L=29, D/L=30
        // From Mixed:  L/L=28, U/L=29, P/L=30
        // From Punct:  U/L=31
        // From Digit:  U/L=14, U/S=15(shift)
        $code = match ([$from, $to]) {
            [self::MODE_UPPER, self::MODE_LOWER] => 28,
            [self::MODE_UPPER, self::MODE_MIXED] => 29,
            [self::MODE_UPPER, self::MODE_DIGIT] => 30,
            [self::MODE_LOWER, self::MODE_MIXED] => 29,
            [self::MODE_LOWER, self::MODE_DIGIT] => 30,
            [self::MODE_LOWER, self::MODE_UPPER] => 28, // U/S — single-char shift, mode stays Lower
            [self::MODE_MIXED, self::MODE_LOWER] => 28,
            [self::MODE_MIXED, self::MODE_UPPER] => 29,
            [self::MODE_MIXED, self::MODE_PUNCT] => 30,
            [self::MODE_MIXED, self::MODE_DIGIT] => self::MODE_MIXED, // double-step
            [self::MODE_PUNCT, self::MODE_UPPER] => 31,
            [self::MODE_DIGIT, self::MODE_UPPER] => 14,
            default => null,
        };

        if ($code === self::MODE_MIXED) {
            // Special: Mixed→Digit needs Mixed→Upper(29) then Upper→Digit(30).
            return str_pad(decbin(29), 5, '0', STR_PAD_LEFT) . str_pad(decbin(30), 5, '0', STR_PAD_LEFT);
        }
        if ($code === null) {
            // Fallback: go via Upper. From=Digit→Lower means D→U(14)+U→L(28).
            $viaUpper = self::switchModeBits($from, self::MODE_UPPER);
            $viaUpper .= self::switchModeBits(self::MODE_UPPER, $to);

            return $viaUpper;
        }

        return str_pad(decbin($code), $fromBits, '0', STR_PAD_LEFT);
    }

    /**
     * Emit binary shift sequence: B/S (5 bits) + length (5 or 11 bits) + 8 bits per byte.
     *
     * @param  list<int>  $bytes
     */
    private static function emitBinaryShift(int $mode, array $bytes): string
    {
        $n = count($bytes);
        $bsCode = $mode === self::MODE_DIGIT ? 15 : 31; // B/S = 31 in 5-bit modes; in Digit no direct B/S — use U/S
        $modeBits = self::MODE_BITS[$mode];
        if ($mode === self::MODE_DIGIT) {
            // From Digit: U/S (single upper shift) — then enter B/S via Upper latch.
            $out = str_pad(decbin(14), 4, '0', STR_PAD_LEFT); // D→U latch.
            $modeBits = 5;
            $bsCode = 31;
            $out .= str_pad(decbin($bsCode), 5, '0', STR_PAD_LEFT);
        } else {
            $out = str_pad(decbin($bsCode), $modeBits, '0', STR_PAD_LEFT);
        }

        // Length: 5 bits if 1-31; else 0 (5 bits) + 11 bits for actual length.
        if ($n <= 31) {
            $out .= str_pad(decbin($n), 5, '0', STR_PAD_LEFT);
        } else {
            $out .= '00000';
            $out .= str_pad(decbin($n - 31), 11, '0', STR_PAD_LEFT);
        }
        foreach ($bytes as $b) {
            $out .= str_pad(decbin($b), 8, '0', STR_PAD_LEFT);
        }

        return $out;
    }

    /**
     * Build character → [(mode, code), ...] mapping. Order of (mode, code)
     * pairs prefers shorter encoding modes.
     */
    private static function classmap(): void
    {
        if (self::$charMap !== null) {
            return;
        }
        $map = [];

        // Upper mode (5-bit): 1=SP, 2-27=A-Z.
        $map[ord(' ')][] = [self::MODE_UPPER, 1];
        for ($i = 0; $i < 26; $i++) {
            $map[ord('A') + $i][] = [self::MODE_UPPER, 2 + $i];
        }

        // Lower mode: 1=SP, 2-27=a-z.
        $map[ord(' ')][] = [self::MODE_LOWER, 1];
        for ($i = 0; $i < 26; $i++) {
            $map[ord('a') + $i][] = [self::MODE_LOWER, 2 + $i];
        }

        // Mixed mode: ctrl chars + special punctuation.
        // 0:P/S, 1:SP, 2:^A, 3:^B, ..., 13:^L, 14:^M (CR), 15:'^', 16:'_', 17:'`', 18:'|', 19:'~', 20:DEL, 21:'@', 22:'\\', 23:'^', 24:'_', 25:'`', 26:'|', 27:'~', 28:LL, 29:UL, 30:PL, 31:BS
        // (simplified — only the common printables + control)
        $map[ord(' ')][] = [self::MODE_MIXED, 1];
        // Control chars 1..13 → codes 2..14 (^A..^M).
        for ($i = 1; $i <= 13; $i++) {
            $map[$i][] = [self::MODE_MIXED, 1 + $i];
        }
        // 14 = ESC (0x1B), 15-19 = FS,GS,RS,US,@.
        $mixedMap = [
            0x1B => 14, 0x1C => 15, 0x1D => 16, 0x1E => 17, 0x1F => 18,
            ord('@') => 19, ord('\\') => 20, ord('^') => 21,
            ord('_') => 22, ord('`') => 23, ord('|') => 24,
            ord('~') => 25, 0x7F => 26,
        ];
        foreach ($mixedMap as $byte => $code) {
            $map[$byte][] = [self::MODE_MIXED, $code];
        }

        // Punct mode: punctuation.
        // 0:FLG(n), 1:CR, 2:CR/LF, 3:.SP, 4:,SP, 5::SP, 6-30: various, 31:U/L
        $punctMap = [
            ord('!') => 6, ord('"') => 7, ord('#') => 8, ord('$') => 9,
            ord('%') => 10, ord('&') => 11, ord('\'') => 12, ord('(') => 13,
            ord(')') => 14, ord('*') => 15, ord('+') => 16, ord(',') => 17,
            ord('-') => 18, ord('.') => 19, ord('/') => 20, ord(':') => 21,
            ord(';') => 22, ord('<') => 23, ord('=') => 24, ord('>') => 25,
            ord('?') => 26, ord('[') => 27, ord(']') => 28,
            ord('{') => 29, ord('}') => 30,
        ];
        foreach ($punctMap as $byte => $code) {
            $map[$byte][] = [self::MODE_PUNCT, $code];
        }

        // Digit mode: 1:SP, 2-11:'0'-'9', 12:',', 13:'.'.
        $map[ord(' ')][] = [self::MODE_DIGIT, 1];
        for ($i = 0; $i <= 9; $i++) {
            $map[ord('0') + $i][] = [self::MODE_DIGIT, 2 + $i];
        }
        $map[ord(',')][] = [self::MODE_DIGIT, 12];
        $map[ord('.')][] = [self::MODE_DIGIT, 13];

        self::$charMap = $map;
    }

    // ---------------- Bit stuffing ----------------

    /**
     * Bit stuffing per ISO/IEC 24778 §6.4.4.
     *
     * Iterates wordSize bits at a time. If first (wordSize-1) bits are all
     * same (1...1 or 0...0), modify the last bit of the codeword:
     *  - All-ones: emit codeword & mask (clear last bit), back up 1 bit.
     *  - All-zeros: emit codeword | 1 (set last bit), back up 1 bit.
     * Otherwise emit codeword as-is.
     *
     * Pads with all-1s if last input bit short of word boundary.
     */
    public static function stuffBits(string $bits, int $wordSize): string
    {
        $n = strlen($bits);
        $mask = (1 << $wordSize) - 2; // all-1s except LSB
        $out = '';
        for ($i = 0; $i < $n; $i += $wordSize) {
            $word = 0;
            for ($j = 0; $j < $wordSize; $j++) {
                if ($i + $j >= $n || $bits[$i + $j] === '1') {
                    $word |= 1 << ($wordSize - 1 - $j);
                }
            }
            if (($word & $mask) === $mask) {
                // First wordSize-1 bits all 1: clear last bit, defer 1 input bit.
                $out .= str_pad(decbin($word & $mask), $wordSize, '0', STR_PAD_LEFT);
                $i--;
            } elseif (($word & $mask) === 0) {
                // First wordSize-1 bits all 0: set last bit, defer 1 input bit.
                $out .= str_pad(decbin($word | 1), $wordSize, '0', STR_PAD_LEFT);
                $i--;
            } else {
                $out .= str_pad(decbin($word), $wordSize, '0', STR_PAD_LEFT);
            }
        }

        return $out;
    }

    /**
     * Generate check (ECC) words via Reed-Solomon.
     *
     * Convention: generator polynomial stored MSB-first (leading coef
     * at index 0). Synthetic division aligns naturally.
     */
    public static function generateCheckWords(string $messageBits, int $totalBits, int $wordSize): string
    {
        $messageSizeInWords = intdiv(strlen($messageBits), $wordSize);
        $totalWords = intdiv($totalBits, $wordSize);
        $eccLen = $totalWords - $messageSizeInWords;
        $primPoly = self::PRIM_POLY[$wordSize]
            ?? throw new \InvalidArgumentException('Unsupported word size: ' . $wordSize);
        $size = 1 << $wordSize;
        [$logTable, $expTable] = self::gfTables($size, $primPoly);

        // Convert message bits → ints (MSB first within each word).
        $messageWords = [];
        for ($i = 0; $i < $messageSizeInWords; $i++) {
            $val = 0;
            for ($j = 0; $j < $wordSize; $j++) {
                if ($messageBits[$i * $wordSize + $j] === '1') {
                    $val |= 1 << ($wordSize - 1 - $j);
                }
            }
            $messageWords[] = $val;
        }

        // Build generator polynomial — MSB-first: [1, α^1] × [1, α^2] × ...
        // gen[0] = leading coefficient (highest degree), gen[eccLen] = constant.
        // For Aztec, generator base is 1 (α^1, α^2, ..., α^eccLen).
        $gen = [1];
        for ($d = 1; $d <= $eccLen; $d++) {
            // Multiply current gen by (x + α^d). Convention: MSB first.
            // [g0, g1, ..., gk] × [1, α^d] = [g0, g0*α^d + g1, g1*α^d + g2, ..., gk*α^d].
            $next = array_fill(0, count($gen) + 1, 0);
            $next[0] = $gen[0];
            for ($j = 1; $j < count($gen); $j++) {
                $next[$j] = self::gfMul($gen[$j - 1], $expTable[$d], $logTable, $expTable, $size) ^ $gen[$j];
            }
            $next[count($gen)] = self::gfMul($gen[count($gen) - 1], $expTable[$d], $logTable, $expTable, $size);
            $gen = $next;
        }

        // Synthetic division: msg × x^eccLen / gen, taking remainder.
        // Buffer: messageWords (length k) followed by eccLen zeros = (k + eccLen) = totalWords.
        $buf = array_merge($messageWords, array_fill(0, $eccLen, 0));
        for ($i = 0; $i < $messageSizeInWords; $i++) {
            $coef = $buf[$i];
            if ($coef !== 0) {
                // gen has length eccLen+1, gen[0]=1 (leading).
                // For j=0..eccLen: buf[i+j] -= gen[j] × coef.
                // Since gen[0]=1, buf[i] -= coef → buf[i] = 0.
                for ($j = 0; $j < count($gen); $j++) {
                    $buf[$i + $j] ^= self::gfMul($gen[$j], $coef, $logTable, $expTable, $size);
                }
            }
        }
        $eccWords = array_slice($buf, $messageSizeInWords, $eccLen);

        // Output bits: startPad + message + ecc.
        $startPad = $totalBits % $wordSize;
        $bits = str_repeat('0', $startPad);
        foreach ($messageWords as $w) {
            $bits .= str_pad(decbin($w), $wordSize, '0', STR_PAD_LEFT);
        }
        foreach ($eccWords as $w) {
            $bits .= str_pad(decbin($w), $wordSize, '0', STR_PAD_LEFT);
        }

        return $bits;
    }

    /**
     * Generate mode message bits.
     * Compact: 2 bits layers-1 + 6 bits messageSize-1 + 20 bits RS ECC = 28 bits.
     * Full:    5 bits layers-1 + 11 bits messageSize-1 + 24 bits RS ECC = 40 bits.
     */
    public static function generateModeMessage(bool $compact, int $layers, int $messageSizeInWords): string
    {
        if ($compact) {
            $info = (($layers - 1) << 6) | ($messageSizeInWords - 1);
            $infoBits = str_pad(decbin($info), 8, '0', STR_PAD_LEFT);

            return self::generateCheckWords($infoBits, 28, 4);
        }
        $info = (($layers - 1) << 11) | ($messageSizeInWords - 1);
        $infoBits = str_pad(decbin($info), 16, '0', STR_PAD_LEFT);

        return self::generateCheckWords($infoBits, 40, 4);
    }

    // ---------------- Reed-Solomon ----------------

    /**
     * Reed-Solomon ECC over GF(2^m).
     *
     * @param  list<int>  $data
     * @return list<int>  ECC codewords
     */
    public static function reedSolomonEncode(array $data, int $eccLen, int $bitsPerCw, int $primPoly): array
    {
        $size = 1 << $bitsPerCw;
        [$logTable, $expTable] = self::gfTables($size, $primPoly);

        // Generator polynomial coefficients.
        $gen = [1];
        for ($i = 0; $i < $eccLen; $i++) {
            $next = array_fill(0, count($gen) + 1, 0);
            foreach ($gen as $j => $c) {
                if ($c !== 0) {
                    $next[$j] = $c ^ self::gfMul($c, 0, $logTable, $expTable, $size); // dummy, replaced below
                }
            }
            // Multiply gen × (x - α^i) = gen × x + gen × α^i (XOR).
            $alpha_i = $expTable[$i];
            $newGen = [];
            $newGen[] = $gen[0]; // x^len term = gen[0]
            for ($j = 1; $j < count($gen); $j++) {
                $newGen[$j] = $gen[$j] ^ self::gfMul($gen[$j - 1], $alpha_i, $logTable, $expTable, $size);
            }
            $newGen[count($gen)] = self::gfMul($gen[count($gen) - 1], $alpha_i, $logTable, $expTable, $size);
            $gen = $newGen;
        }

        // Polynomial division: data || zeros divided by gen.
        $msg = array_merge($data, array_fill(0, $eccLen, 0));
        for ($i = 0; $i < count($data); $i++) {
            $coef = $msg[$i];
            if ($coef !== 0) {
                for ($j = 0; $j < count($gen); $j++) {
                    $msg[$i + $j] ^= self::gfMul($gen[$j], $coef, $logTable, $expTable, $size);
                }
            }
        }

        return array_slice($msg, count($data), $eccLen);
    }

    /**
     * @return array{0: list<int>, 1: list<int>}
     */
    private static function gfTables(int $size, int $primPoly): array
    {
        static $cache = [];
        $key = "$size:$primPoly";
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        $exp = array_fill(0, $size * 2, 0);
        $log = array_fill(0, $size, 0);
        $x = 1;
        for ($i = 0; $i < $size - 1; $i++) {
            $exp[$i] = $x;
            $log[$x] = $i;
            $x <<= 1;
            if ($x & $size) {
                $x ^= $primPoly;
            }
        }
        for ($i = $size - 1; $i < 2 * $size; $i++) {
            $exp[$i] = $exp[$i - ($size - 1)];
        }

        return $cache[$key] = [$log, $exp];
    }

    /**
     * @param list<int> $logTable
     * @param list<int> $expTable
     */
    private static function gfMul(int $a, int $b, array $logTable, array $expTable, int $size): int
    {
        if ($a === 0 || $b === 0) {
            return 0;
        }

        return $expTable[($logTable[$a] + $logTable[$b]) % ($size - 1)];
    }

    // ---------------- Matrix layout ----------------

    /**
     * Build Aztec matrix (Compact or Full).
     *
     *  1. Build alignment map (identity for Compact, sparse for Full).
     *  2. Draw data spiral via alignmentMap.
     *  3. Draw mode message (28 bits compact / 40 bits full).
     *  4. Draw bullseye (5-ring compact / 7-ring full) with orientation cells.
     *  5. Draw alignment grid lines (Full only).
     *
     * @return list<list<bool>>
     */
    private function buildMatrix(string $messageBits, string $modeBits): array
    {
        $base = $this->baseMatrixSize;
        $matrixSize = $this->size;
        $m = array_fill(0, $matrixSize, array_fill(0, $matrixSize, false));

        // Build alignment map.
        $alignmentMap = array_fill(0, $base, 0);
        if ($this->compact) {
            for ($i = 0; $i < $base; $i++) {
                $alignmentMap[$i] = $i;
            }
        } else {
            $origCenter = intdiv($base, 2);
            $center = intdiv($matrixSize, 2);
            for ($i = 0; $i < $origCenter; $i++) {
                $newOffset = $i + intdiv($i, 15);
                $alignmentMap[$origCenter - $i - 1] = $center - $newOffset - 1;
                $alignmentMap[$origCenter + $i] = $center + $newOffset + 1;
            }
        }

        // 1. Data spiral — 4 sides per layer, outermost layer first.
        $sideOffset = $this->compact ? 9 : 12;
        $rowOffset = 0;
        for ($i = 0; $i < $this->layers; $i++) {
            $rowSize = ($this->layers - $i) * 4 + $sideOffset;
            for ($j = 0; $j < $rowSize; $j++) {
                $columnOffset = $j * 2;
                for ($k = 0; $k < 2; $k++) {
                    // Top side (left edge in logical coords).
                    if (self::bitAt($messageBits, $rowOffset + $columnOffset + $k)) {
                        $m[$alignmentMap[$i * 2 + $j]][$alignmentMap[$i * 2 + $k]] = true;
                    }
                    // Right side (top edge).
                    if (self::bitAt($messageBits, $rowOffset + $rowSize * 2 + $columnOffset + $k)) {
                        $m[$alignmentMap[$base - 1 - $i * 2 - $k]][$alignmentMap[$i * 2 + $j]] = true;
                    }
                    // Bottom side (right edge).
                    if (self::bitAt($messageBits, $rowOffset + $rowSize * 4 + $columnOffset + $k)) {
                        $m[$alignmentMap[$base - 1 - $i * 2 - $j]][$alignmentMap[$base - 1 - $i * 2 - $k]] = true;
                    }
                    // Left side (bottom edge).
                    if (self::bitAt($messageBits, $rowOffset + $rowSize * 6 + $columnOffset + $k)) {
                        $m[$alignmentMap[$i * 2 + $k]][$alignmentMap[$base - 1 - $i * 2 - $j]] = true;
                    }
                }
            }
            $rowOffset += $rowSize * 8;
        }

        // 2. Mode message.
        $center = intdiv($matrixSize, 2);
        if ($this->compact) {
            $this->drawCompactModeMessage($m, $center, $modeBits);
        } else {
            $this->drawFullModeMessage($m, $center, $modeBits);
        }

        // 3. Bullseye + orientation cells.
        $this->drawBullsEye($m, $center, $this->compact ? 5 : 7);

        // 4. Alignment grid lines (Full only).
        if (! $this->compact) {
            $this->drawAlignmentGrid($m, $matrixSize, $base);
        }

        return $m;
    }

    private static function bitAt(string $bits, int $i): bool
    {
        return isset($bits[$i]) && $bits[$i] === '1';
    }

    /**
     * Draw concentric square frames at distance 0, 2, ..., size-1 from
     * center. Compact: size=5 (3 frames). Full: size=7 (4 frames).
     * Plus 6 fixed orientation cells just outside bullseye.
     *
     * @param array<int, array<int, bool>> $m
     */
    private function drawBullsEye(array &$m, int $center, int $size): void
    {
        for ($i = 0; $i < $size; $i += 2) {
            for ($j = $center - $i; $j <= $center + $i; $j++) {
                $m[$center - $i][$j] = true;
                $m[$center + $i][$j] = true;
                $m[$j][$center - $i] = true;
                $m[$j][$center + $i] = true;
            }
        }
        // 6 orientation cells.
        $m[$center - $size][$center - $size] = true;
        $m[$center - $size][$center - $size + 1] = true;
        $m[$center - $size + 1][$center - $size] = true;
        $m[$center - $size][$center + $size] = true;
        $m[$center - $size + 1][$center + $size] = true;
        $m[$center + $size - 1][$center + $size] = true;
    }

    /**
     * Compact: 28-bit mode message in 7-cell groups on 4 sides
     * (rows c-5, c+5; cols c-5, c+5; offsets c-3..c+3).
     *
     * @param array<int, array<int, bool>> $m
     */
    private function drawCompactModeMessage(array &$m, int $center, string $modeBits): void
    {
        for ($i = 0; $i < 7; $i++) {
            $offset = $center - 3 + $i;
            if (self::bitAt($modeBits, $i)) {
                $m[$center - 5][$offset] = true;
            }
            if (self::bitAt($modeBits, $i + 7)) {
                $m[$offset][$center + 5] = true;
            }
            if (self::bitAt($modeBits, 20 - $i)) {
                $m[$center + 5][$offset] = true;
            }
            if (self::bitAt($modeBits, 27 - $i)) {
                $m[$offset][$center - 5] = true;
            }
        }
    }

    /**
     * Full: 40-bit mode message in 10-cell groups on 4 sides
     * (rows c-7, c+7; cols c-7, c+7).
     * Offset formula: c - 5 + i + i/5 (skips center alignment cell at i=5).
     *
     * @param array<int, array<int, bool>> $m
     */
    private function drawFullModeMessage(array &$m, int $center, string $modeBits): void
    {
        for ($i = 0; $i < 10; $i++) {
            $offset = $center - 5 + $i + intdiv($i, 5);
            if (self::bitAt($modeBits, $i)) {
                $m[$center - 7][$offset] = true;
            }
            if (self::bitAt($modeBits, $i + 10)) {
                $m[$offset][$center + 7] = true;
            }
            if (self::bitAt($modeBits, 29 - $i)) {
                $m[$center + 7][$offset] = true;
            }
            if (self::bitAt($modeBits, 39 - $i)) {
                $m[$offset][$center - 7] = true;
            }
        }
    }

    /**
     * Full Aztec reference grid lines — dotted vertical + horizontal lines
     * at center and every 16 modules from center.
     *
     * @param array<int, array<int, bool>> $m
     */
    private function drawAlignmentGrid(array &$m, int $matrixSize, int $base): void
    {
        $center = intdiv($matrixSize, 2);
        $startParity = $center & 1; // even cells get black if center is even
        for ($i = 0, $j = 0; $i < intdiv($base, 2) - 1; $i += 15, $j += 16) {
            for ($k = $startParity; $k < $matrixSize; $k += 2) {
                $m[$k][$center - $j] = true;
                $m[$k][$center + $j] = true;
                $m[$center - $j][$k] = true;
                $m[$center + $j][$k] = true;
            }
        }
    }
}
