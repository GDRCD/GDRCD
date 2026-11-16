<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * PDF417 stacked linear 2D barcode (ISO/IEC 15438:2006).
 *
 * Stacked rows of 17-module codewords with Reed-Solomon ECC over GF(929).
 * Each row: start pattern + left row indicator + N data codewords +
 * right row indicator + stop pattern.
 *
 * Supported:
 *  - Byte compaction mode (codeword 901): handles arbitrary ASCII + binary.
 *    Each input byte → 1 PDF417 codeword.
 *  - Multi-byte compaction (codeword 924): groups of 6 bytes → 5 codewords
 *    (used when data length is a multiple of 6).
 *  - Text compaction (codeword 900): denser for alphanumeric.
 *  - Numeric compaction (codeword 902): denser for digit-only.
 *  - All ECC levels 0..8 (2 to 512 ECC codewords).
 *  - Auto-dimension: chooses rows × cols based on aspect ratio target.
 *  - Macro PDF417 (multi-symbol concatenation), GS1 FNC1, ECI.
 *
 * Output via {@see modules()} — 2D bool matrix.
 */
final class Pdf417Encoder
{
    /** Single-byte mode latch codeword. */
    private const MODE_LATCH_BYTE_SINGLE = 901;

    /** Multi-byte mode latch (used when data length is multiple of 6). */
    private const MODE_LATCH_BYTE_MULTI = 924;

    /** Pad codeword (Text mode latch — used here for padding). */
    private const PADDING_CODEWORD = 900;

    // Compaction mode constants.
    public const MODE_AUTO = 'auto';

    public const MODE_BYTE = 'byte';

    public const MODE_TEXT = 'text';

    public const MODE_NUMERIC = 'numeric';

    /** Text compaction mode latch (codeword 900, = padding codeword). */
    private const MODE_LATCH_TEXT = 900;

    /** Numeric compaction mode latch. */
    private const MODE_LATCH_NUMERIC = 902;

    // Macro PDF417 control codewords (ISO/IEC 15438 §5.5).
    private const MACRO_INDICATOR = 928;

    private const MACRO_TERMINATOR = 922;

    private const MACRO_FIELD_MARK = 923;

    // ECI + FNC1 indicator codewords (ISO/IEC 15438 §5.4.1.5).
    /** ECI Character Set — followed by designator (1-3 codewords). */
    public const ECI_CHARSET = 927;

    /** ECI General Purpose — alternative ECI form. */
    public const ECI_GENERAL = 926;

    /** FNC1 codeword — GS1 PDF417 marker. */
    public const FNC1 = 920;

    public readonly int $rows;

    public readonly int $cols;

    public readonly int $eccLevel;

    /** @var list<int> All codewords incl. SLD + data + ECC. */
    public readonly array $codewords;

    /** @var list<list<bool>> Final matrix; rows × (cols×17 + 17 + 17 + 18) modules. */
    private array $matrix;

    /**
     * @param  string  $data            arbitrary bytes
     * @param  int     $eccLevel        0..8 (defaults to auto based on data size)
     * @param  float   $aspectRatio     desired width/height ratio (TCPDF default 2.0)
     * @param  array{index: int, total: int, fileId: int}|null  $macroSegment  Macro PDF417 control block
     */
    public function __construct(
        public readonly string $data,
        ?int $eccLevel = null,
        float $aspectRatio = 2.0,
        string $mode = self::MODE_BYTE,
        ?array $macroSegment = null,
        bool $gs1 = false,
        ?int $eciDesignator = null,
    ) {
        if ($data === '') {
            throw new \InvalidArgumentException('PDF417 input must be non-empty');
        }

        // 1. Mode-based compaction.
        $codewords = match ($mode) {
            self::MODE_BYTE => self::byteCompaction($data),
            self::MODE_TEXT => self::textCompaction($data),
            self::MODE_NUMERIC => self::numericCompaction($data),
            self::MODE_AUTO => self::autoCompaction($data),
            default => throw new \InvalidArgumentException("Unknown PDF417 mode: $mode"),
        };

        // Prepend Macro control block (if macroSegment is set).
        if ($macroSegment !== null) {
            $macroHeader = [self::MACRO_INDICATOR];
            // Segment index — encoded as 5-digit numeric via numericCompaction inline.
            $segStr = str_pad((string) $macroSegment['index'], 5, '0', STR_PAD_LEFT);
            // Append digits in numeric mode (no mode latch — already in macro context).
            $segNumeric = self::convertBaseBigInt('1'.$segStr, 10, 900);
            foreach ($segNumeric as $v) {
                $macroHeader[] = $v;
            }
            // File ID — also numeric, 3-digit.
            $fileStr = str_pad((string) $macroSegment['fileId'], 3, '0', STR_PAD_LEFT);
            $fileNumeric = self::convertBaseBigInt('1'.$fileStr, 10, 900);
            foreach ($fileNumeric as $v) {
                $macroHeader[] = $v;
            }
            // Optional field marker 923 + tag 1 + total_segments numeric.
            $macroHeader[] = self::MACRO_FIELD_MARK;
            $macroHeader[] = 1; // tag = total segment count
            $totalStr = str_pad((string) $macroSegment['total'], 5, '0', STR_PAD_LEFT);
            $totalNumeric = self::convertBaseBigInt('1'.$totalStr, 10, 900);
            foreach ($totalNumeric as $v) {
                $macroHeader[] = $v;
            }
            // Last segment: append 922 terminator.
            $isLast = $macroSegment['index'] === $macroSegment['total'] - 1;
            if ($isLast) {
                $macroHeader[] = self::MACRO_TERMINATOR;
            }
            $codewords = array_merge($macroHeader, $codewords);
        }
        // GS1 FNC1 marker (CW 920) prepended.
        if ($gs1) {
            array_unshift($codewords, self::FNC1);
        }
        // ECI marker (CW 927) + designator codewords prepended.
        if ($eciDesignator !== null) {
            if ($eciDesignator < 0 || $eciDesignator > 811799) {
                throw new \InvalidArgumentException('PDF417 ECI designator must be 0..811799');
            }
            // ECI encoding per ISO 15438 §5.4.1.5:
            //  0..899: 1 codeword 927 + designator
            //  900..810899: 2 codewords 927 + (designator / 900) + designator % 900
            //  810900..811799: 3 codewords (rarely used).
            $eciCw = [self::ECI_CHARSET];
            if ($eciDesignator <= 899) {
                $eciCw[] = $eciDesignator;
            } else {
                $eciCw[] = intdiv($eciDesignator, 900);
                $eciCw[] = $eciDesignator % 900;
            }
            $codewords = array_merge($eciCw, $codewords);
        }
        $numDataCw = count($codewords);

        // 2. Select ECC level (auto): scale with data size per ISO §5.3.6.
        if ($eccLevel === null) {
            $eccLevel = self::autoEccLevel($numDataCw);
        }
        if ($eccLevel < 0 || $eccLevel > 8) {
            throw new \InvalidArgumentException('PDF417 ECC level must be 0..8');
        }
        $this->eccLevel = $eccLevel;

        $eccSize = 2 << $eccLevel; // 2,4,8,16,32,64,128,256,512.

        // Total codewords: SLD (1) + data + ECC.
        $nce = $numDataCw + $eccSize + 1;
        if ($nce > 929) {
            throw new \InvalidArgumentException(sprintf(
                'PDF417 capacity exceeded: %d codewords > 929 max',
                $nce,
            ));
        }

        // 3. Choose cols/rows balancing aspect ratio.
        // Formula derived in ISO §5.3.6 — cols ≈ sqrt(4761 + 272*aspect*nce)/34 - 2.
        $rowHeightModules = 4; // ROWHEIGHT constant.
        $cols = (int) round((sqrt(4761 + 68 * $aspectRatio * $rowHeightModules * $nce) - 69) / 34);
        $cols = max(1, min(30, $cols));
        $rows = (int) ceil($nce / $cols);
        if ($rows < 3) {
            $rows = 3;
        } elseif ($rows > 90) {
            $rows = 90;
            $cols = (int) ceil($nce / $rows);
        }
        $size = $rows * $cols;

        // 4. Pad with 900 (text mode latch is the canonical padding codeword).
        $pad = $size - $nce;
        if ($pad > 0) {
            $codewords = array_merge($codewords, array_fill(0, $pad, self::PADDING_CODEWORD));
        }

        // 5. Prepend Symbol Length Descriptor.
        $sld = $size - $eccSize;
        array_unshift($codewords, $sld);

        // 6. Compute Reed-Solomon ECC over GF(929).
        $ecw = self::reedSolomonEncode($codewords, $eccLevel);
        $codewords = array_merge($codewords, $ecw);

        $this->codewords = $codewords;
        $this->rows = $rows;
        $this->cols = $cols;

        // 7. Build matrix.
        $this->matrix = $this->buildMatrix($codewords, $rows, $cols, $eccLevel);
    }

    /**
     * @return list<list<bool>>
     */
    public function modules(): array
    {
        return $this->matrix;
    }

    public function rowCount(): int
    {
        return count($this->matrix);
    }

    public function colCount(): int
    {
        return count($this->matrix[0]);
    }

    /**
     * PDF417 byte compaction per ISO/IEC 15438 §5.4.2.4.
     *
     * Latch codeword 924 (used when len % 6 == 0): every 6 bytes pack as
     * base-900 → 5 codewords.
     *
     * Latch codeword 901 (used when len % 6 != 0): same 6-byte groups +
     * tail bytes (1-5) encoded as 1 codeword per byte (raw value 0-255).
     *
     * The 1-byte-per-codeword tail mode is what makes 901 distinct from
     * 924 — both use base-900 packing for full groups.
     *
     * @return list<int>
     */
    public static function byteCompaction(string $data): array
    {
        $len = strlen($data);
        $cw = [];
        $cw[] = ($len % 6 === 0) ? self::MODE_LATCH_BYTE_MULTI : self::MODE_LATCH_BYTE_SINGLE;
        // Full groups of 6 bytes → 5 codewords each (base-900 packing).
        $fullGroups = intdiv($len, 6);
        for ($g = 0; $g < $fullGroups; $g++) {
            $cw = array_merge($cw, self::packSixBytes(substr($data, $g * 6, 6)));
        }
        // Tail bytes (1-5) emitted as raw byte codewords (after full groups).
        for ($i = $fullGroups * 6; $i < $len; $i++) {
            $cw[] = ord($data[$i]);
        }

        return $cw;
    }

    /**
     * Pack 6 bytes as big-endian 48-bit integer into 5 base-900 digits.
     *
     * @return list<int>
     */
    private static function packSixBytes(string $bytes): array
    {
        // 48-bit value — fits in PHP int on 64-bit platforms.
        $value = 0;
        for ($i = 0; $i < 6; $i++) {
            $value = ($value << 8) | ord($bytes[$i]);
        }
        $digits = [0, 0, 0, 0, 0];
        for ($i = 4; $i >= 0; $i--) {
            $digits[$i] = (int) ($value % 900);
            $value = (int) ($value / 900);
        }

        return $digits;
    }

    /**
     * Reed-Solomon ECC over GF(929).
     *
     * @param  list<int>  $data
     * @return list<int>
     */
    public static function reedSolomonEncode(array $data, int $eccLevel): array
    {
        $factors = Pdf417Patterns::RS_FACTORS[$eccLevel];
        $eccLen = count($factors);
        $ecc = array_fill(0, $eccLen, 0);
        foreach ($data as $d) {
            $t1 = ($d + $ecc[$eccLen - 1]) % 929;
            for ($j = $eccLen - 1; $j > 0; $j--) {
                $t2 = ($t1 * $factors[$j]) % 929;
                $ecc[$j] = ($ecc[$j - 1] + 929 - $t2) % 929;
            }
            $t2 = ($t1 * $factors[0]) % 929;
            $ecc[0] = (929 - $t2) % 929;
        }
        foreach ($ecc as $j => $e) {
            if ($e !== 0) {
                $ecc[$j] = 929 - $e;
            }
        }

        return array_reverse($ecc);
    }

    /**
     * Macro PDF417 segment factory.
     *
     * Per ISO/IEC 15438 §5.5 — used when data exceeds single-symbol capacity
     * (~ 925 codewords). Each segment encodes as a separate Pdf417Encoder
     * with a Macro control block prepended.
     *
     * Macro control structure:
     *   928 (indicator) + segment_index_5_digits_numeric + file_id_numeric +
     *   [optional fields] + user data + [922 terminator on last segment]
     *
     * Simple API: caller splits data, creates one segment per call.
     *
     * @param  string  $data  segment user data (chunk of total)
     * @param  int     $segmentIndex  0-based segment number (0..99998)
     * @param  int     $totalSegments  total segments (used to determine last)
     * @param  int     $fileId  numeric file identifier (0..899)
     * @param  string  $mode    compaction mode for user data
     * @param  ?int    $eccLevel
     */
    public static function macroSegment(
        string $data,
        int $segmentIndex,
        int $totalSegments,
        int $fileId,
        string $mode = self::MODE_BYTE,
        ?int $eccLevel = null,
    ): self {
        if ($segmentIndex < 0 || $segmentIndex > 99998) {
            throw new \InvalidArgumentException('Macro PDF417 segment index must be 0..99998');
        }
        if ($totalSegments < 1 || $totalSegments > 99999) {
            throw new \InvalidArgumentException('Macro PDF417 total segments must be 1..99999');
        }
        if ($segmentIndex >= $totalSegments) {
            throw new \InvalidArgumentException(sprintf(
                'segment index %d not in range 0..%d', $segmentIndex, $totalSegments - 1
            ));
        }
        if ($fileId < 0 || $fileId > 899) {
            throw new \InvalidArgumentException('Macro PDF417 file ID must be 0..899');
        }

        return new self(
            data: $data,
            eccLevel: $eccLevel,
            aspectRatio: 2.0,
            mode: $mode,
            macroSegment: [
                'index' => $segmentIndex,
                'total' => $totalSegments,
                'fileId' => $fileId,
            ],
        );
    }

    /**
     * Text compaction per ISO/IEC 15438 §5.4.2.
     *
     * 2 chars / 1 codeword (each char 5 bits, packed 30*ch1 + ch2).
     * Sub-modes: Alpha (uppercase), Lower (lowercase), Mixed (digits +
     * common punct), Punctuation (rare punct).
     *
     * Compaction control codes within text stream (values 0-29 each sub-mode):
     *   Alpha:  A-Z + SP, LL=27, ML=28, PS=29
     *   Lower:  a-z + SP, AS=27, ML=28, PS=29
     *   Mixed:  0-9 + &/.-:#  etc, LL=27, AL=28, PL=25
     *   Punct:  punct chars,        AL=29
     *
     * Mode latch CW 900 inserted before chars.
     *
     * @return list<int>
     */
    public static function textCompaction(string $data): array
    {
        $cw = [self::MODE_LATCH_TEXT];
        $values = []; // 5-bit values
        $currentSub = 'alpha'; // start in alpha sub-mode

        $n = strlen($data);
        for ($i = 0; $i < $n; $i++) {
            $b = ord($data[$i]);
            // Determine target sub-mode for this char.
            if ($b >= 65 && $b <= 90) { // A-Z
                $target = 'alpha';
                $v = $b - 65;
            } elseif ($b >= 97 && $b <= 122) { // a-z
                $target = 'lower';
                $v = $b - 97;
            } elseif ($b >= 48 && $b <= 57) { // 0-9
                $target = 'mixed';
                $v = $b - 48;
            } elseif ($b === 32) { // space — universal in all sub-modes
                $target = $currentSub;
                $v = 26; // SP value
            } else {
                // Punctuation — extremely simplified: encode as Mixed sub-mode value
                // for & (10), \r (11), \t (12), , (13), : (14), # (15), -(16), . (17),
                // $ (18), / (19), + (20), % (21), * (22), = (23), ^ (24).
                // Bytes outside common punct — skip (fallback to byte mode not implemented).
                $mixedPunct = [38 => 10, 13 => 11, 9 => 12, 44 => 13, 58 => 14, 35 => 15,
                    45 => 16, 46 => 17, 36 => 18, 47 => 19, 43 => 20, 37 => 21, 42 => 22,
                    61 => 23, 94 => 24];
                if (isset($mixedPunct[$b])) {
                    $target = 'mixed';
                    $v = $mixedPunct[$b];
                } else {
                    throw new \InvalidArgumentException(sprintf(
                        'PDF417 Text mode does not support byte 0x%02X', $b
                    ));
                }
            }
            // Emit shift if sub-mode changes.
            if ($target !== $currentSub) {
                if ($currentSub === 'alpha' && $target === 'lower') {
                    $values[] = 27; // LL
                } elseif ($currentSub === 'alpha' && $target === 'mixed') {
                    $values[] = 28; // ML
                } elseif ($currentSub === 'lower' && $target === 'alpha') {
                    $values[] = 28; // ML, then AL
                    $values[] = 28; // AL (28 in Mixed = to Alpha)
                } elseif ($currentSub === 'lower' && $target === 'mixed') {
                    $values[] = 28; // ML
                } elseif ($currentSub === 'mixed' && $target === 'alpha') {
                    $values[] = 28; // AL
                } elseif ($currentSub === 'mixed' && $target === 'lower') {
                    $values[] = 27; // LL
                }
                $currentSub = $target;
            }
            $values[] = $v;
        }
        // Pad to even count with 29 (Pad in Alpha) or equivalent.
        if (count($values) % 2 !== 0) {
            $values[] = 29;
        }
        // Pack pairs: cw = 30*v1 + v2.
        for ($i = 0; $i < count($values); $i += 2) {
            $cw[] = 30 * $values[$i] + $values[$i + 1];
        }

        return $cw;
    }

    /**
     * Numeric compaction per ISO/IEC 15438 §5.4.3.
     *
     * Groups of ≤44 digits: prepend "1" → base 900 conversion → output codewords.
     * 44 digits → 15 codewords (avg ~2.93 digits/CW vs byte 1.2 digits/CW).
     *
     * Mode latch CW 902 inserted before digits.
     *
     * @return list<int>
     */
    public static function numericCompaction(string $data): array
    {
        if (! ctype_digit($data)) {
            throw new \InvalidArgumentException('PDF417 Numeric mode requires digit-only input');
        }
        $cw = [self::MODE_LATCH_NUMERIC];
        $n = strlen($data);
        for ($offset = 0; $offset < $n; $offset += 44) {
            $chunk = substr($data, $offset, 44);
            // Prepend "1" to chunk, convert big number to base 900.
            $bigNum = '1'.$chunk;
            $base900 = self::convertBaseBigInt($bigNum, 10, 900);
            foreach ($base900 as $v) {
                $cw[] = $v;
            }
        }

        return $cw;
    }

    /**
     * Convert big-int string from base $fromBase to base $toBase.
     * Returns list<int> in MSB-first order.
     *
     * @return list<int>
     */
    private static function convertBaseBigInt(string $num, int $fromBase, int $toBase): array
    {
        // Convert string of digits (base $fromBase) to array of ints in base $toBase.
        // Simple long division algorithm.
        $digits = array_map('intval', str_split($num));
        $result = [];
        while (count($digits) > 0) {
            $remainder = 0;
            $newDigits = [];
            foreach ($digits as $d) {
                $val = $remainder * $fromBase + $d;
                $newDigits[] = intdiv($val, $toBase);
                $remainder = $val % $toBase;
            }
            // Strip leading zeros in newDigits.
            while (count($newDigits) > 0 && $newDigits[0] === 0) {
                array_shift($newDigits);
            }
            $result[] = $remainder;
            $digits = $newDigits;
        }

        return array_reverse($result);
    }

    /**
     * Auto-pick best compaction mode per input content.
     *
     * @return list<int>
     */
    public static function autoCompaction(string $data): array
    {
        // Pure digits → Numeric (most compact).
        if (ctype_digit($data) && strlen($data) >= 13) {
            return self::numericCompaction($data);
        }
        // Pure ASCII letters/digits/common punct → Text.
        $textEligible = true;
        for ($i = 0; $i < strlen($data); $i++) {
            $b = ord($data[$i]);
            // Quick check: A-Z, a-z, 0-9, SP, common punct.
            if (! (($b >= 32 && $b <= 122 && ! ($b >= 91 && $b <= 96)))) {
                $textEligible = false;
                break;
            }
        }
        if ($textEligible && strlen($data) >= 6) {
            try {
                return self::textCompaction($data);
            } catch (\InvalidArgumentException) {
                // Fall through to byte.
            }
        }

        return self::byteCompaction($data);
    }

    /**
     * Auto-select ECC level per ISO/IEC 15438 §5.3.6 Table 8 recommendation.
     */
    private static function autoEccLevel(int $numDataCw): int
    {
        return match (true) {
            $numDataCw <= 40 => 2,
            $numDataCw <= 160 => 3,
            $numDataCw <= 320 => 4,
            $numDataCw <= 863 => 5,
            default => 6,
        };
    }

    /**
     * Build the 2D module matrix.
     *
     * Each row: 17 (start) + 17 (left RI) + cols×17 (data) + 17 (right RI) + 18 (stop).
     *
     * @param  list<int>  $codewords  size = rows × cols
     * @return list<list<bool>>
     */
    private function buildMatrix(array $codewords, int $rows, int $cols, int $ecl): array
    {
        $matrix = [];
        $k = 0;
        $totalWidth = 17 + 17 + $cols * 17 + 17 + 18;

        for ($r = 0; $r < $rows; $r++) {
            // Cluster index 0/1/2 maps to cluster IDs 0/3/6.
            $cid = $r % 3;
            $rowBits = [];

            // Start pattern (17 bits).
            self::appendPattern($rowBits, Pdf417Patterns::START_PATTERN, 17);

            // Left row indicator.
            $L = $this->leftRowIndicator($r, $cid, $rows, $cols, $ecl);
            self::appendPattern($rowBits, Pdf417Patterns::PATTERNS[$cid][$L], 17);

            // Data codewords.
            for ($c = 0; $c < $cols; $c++) {
                self::appendPattern($rowBits, Pdf417Patterns::PATTERNS[$cid][$codewords[$k++]], 17);
            }

            // Right row indicator.
            $R = $this->rightRowIndicator($r, $cid, $rows, $cols, $ecl);
            self::appendPattern($rowBits, Pdf417Patterns::PATTERNS[$cid][$R], 17);

            // Stop pattern (18 bits, ends with extra trailing bar).
            self::appendPattern($rowBits, Pdf417Patterns::STOP_PATTERN, 18);

            // Each row repeats vertically rowHeight times — typically 3 modules
            // tall (ROWHEIGHT in ISO). Returning 1 row per codeword row keeps
            // matrix compact; render layer handles vertical scale.
            $matrix[] = $rowBits;
        }

        return $matrix;
    }

    /**
     * @param  list<bool>  $rowBits
     */
    private static function appendPattern(array &$rowBits, int $pattern, int $bits): void
    {
        for ($i = $bits - 1; $i >= 0; $i--) {
            $rowBits[] = (($pattern >> $i) & 1) === 1;
        }
    }

    private function leftRowIndicator(int $row, int $cid, int $rows, int $cols, int $ecl): int
    {
        $base = 30 * intdiv($row, 3);

        return match ($cid) {
            0 => $base + intdiv($rows - 1, 3),
            1 => $base + ($ecl * 3) + (($rows - 1) % 3),
            2 => $base + ($cols - 1),
            default => throw new \LogicException('invalid cluster'),
        };
    }

    private function rightRowIndicator(int $row, int $cid, int $rows, int $cols, int $ecl): int
    {
        $base = 30 * intdiv($row, 3);

        return match ($cid) {
            0 => $base + ($cols - 1),
            1 => $base + intdiv($rows - 1, 3),
            2 => $base + ($ecl * 3) + (($rows - 1) % 3),
            default => throw new \LogicException('invalid cluster'),
        };
    }
}
