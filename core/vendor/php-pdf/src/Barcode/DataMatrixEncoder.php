<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * DataMatrix ECC 200 encoder (ISO/IEC 16022).
 *
 * Supported:
 *  - All 24 square ECC 200 sizes (10×10 .. 144×144).
 *  - All 6 rectangular sizes (8×18 .. 16×48).
 *  - ASCII, C40, Text, X12, EDIFACT, Base 256 encoding modes.
 *  - Digit pair compression in ASCII mode.
 *  - Multi-region symbols (32+ sizes) with regional finder/timing patterns.
 *  - Interleaved Reed-Solomon ECC for symbols ≥ 52×52.
 *  - GS1 DataMatrix (FNC1) and ECI (extended channel interpretation).
 *  - Macro 05/06 mode markers.
 *
 * Not implemented:
 *  - Structured Append.
 *
 * Output via modules() — 2D bool matrix; true = black module.
 */
final class DataMatrixEncoder
{
    /**
     * Symbol info table. Each row:
     *   [rectangular, dataCapacity, errorCodewords, matrixWidth, matrixHeight,
     *    dataRegions, rsBlockData, rsBlockError]
     *
     * matrixWidth / matrixHeight — dimensions of a SINGLE data region
     * (without finder/timing borders).
     * dataRegions — total regions (1, 2, 4, 16, 36).
     */
    private const SYMBOLS = [
        // [rect, dataCap, eccCap, mW, mH, regions, rsBlockData, rsBlockEcc, blockCountOverride?]
        // blockCountOverride defaults to intdiv(dataCap, rsBlockData); explicit
        // value required when dataCap does not divide cleanly (144×144 case).
        [false, 3,    5,   8,  8,   1,  3,    5],   // 10×10
        [false, 5,    7,   10, 10,  1,  5,    7],   // 12×12
        [true,  5,    7,   16, 6,   1,  5,    7],   // 8×18 rect
        [false, 8,    10,  12, 12,  1,  8,    10],  // 14×14
        [true,  10,   11,  14, 6,   2,  10,   11],  // 8×32 rect
        [false, 12,   12,  14, 14,  1,  12,   12],  // 16×16
        [true,  16,   14,  24, 10,  1,  16,   14],  // 12×26 rect
        [false, 18,   14,  16, 16,  1,  18,   14],  // 18×18
        [false, 22,   18,  18, 18,  1,  22,   18],  // 20×20
        [true,  22,   18,  16, 10,  2,  22,   18],  // 12×36 rect
        [false, 30,   20,  20, 20,  1,  30,   20],  // 22×22
        [true,  32,   24,  16, 14,  2,  32,   24],  // 16×36 rect
        [false, 36,   24,  22, 22,  1,  36,   24],  // 24×24
        [false, 44,   28,  24, 24,  1,  44,   28],  // 26×26
        [true,  49,   28,  22, 14,  2,  49,   28],  // 16×48 rect
        [false, 62,   36,  14, 14,  4,  62,   36],  // 32×32
        [false, 86,   42,  16, 16,  4,  86,   42],  // 36×36
        [false, 114,  48,  18, 18,  4,  114,  48],  // 40×40
        [false, 144,  56,  20, 20,  4,  144,  56],  // 44×44
        [false, 174,  68,  22, 22,  4,  174,  68],  // 48×48
        [false, 204,  84,  24, 24,  4,  102,  42],  // 52×52 (2 RS blocks)
        [false, 280,  112, 14, 14,  16, 140,  56],  // 64×64
        [false, 368,  144, 16, 16,  16, 92,   36],  // 72×72
        [false, 456,  192, 18, 18,  16, 114,  48],  // 80×80
        [false, 576,  224, 20, 20,  16, 144,  56],  // 88×88
        [false, 696,  272, 22, 22,  16, 174,  68],  // 96×96
        [false, 816,  336, 24, 24,  16, 136,  56],  // 104×104
        [false, 1050, 408, 18, 18,  36, 175,  68],  // 120×120
        [false, 1304, 496, 20, 20,  36, 163,  62],  // 132×132
        // 144×144 special — 10 blocks (8×156 + 2×155 distribution via
        // round-robin interleaving), 620 ECC (10×62), 36 regions of
        // 22×22 modules.
        [false, 1558, 620, 22, 22, 36, 156, 62, 10],  // 144×144
    ];

    /**
     * RS factor polynomials per error correction codeword count.
     */

    private const FACTORS = [
        5  => [228, 48, 15, 111, 62],
        7  => [23, 68, 144, 134, 240, 92, 254],
        10 => [28, 24, 185, 166, 223, 248, 116, 255, 110, 61],
        11 => [175, 138, 205, 12, 194, 168, 39, 245, 60, 97, 120],
        12 => [41, 153, 158, 91, 61, 42, 142, 213, 97, 178, 100, 242],
        14 => [156, 97, 192, 252, 95, 9, 157, 119, 138, 45, 18, 186, 83, 185],
        18 => [83, 195, 100, 39, 188, 75, 66, 61, 241, 213, 109, 129, 94, 254, 225, 48, 90, 188],
        20 => [15, 195, 244, 9, 233, 71, 168, 2, 188, 160, 153, 145, 253, 79, 108, 82, 27, 174, 186, 172],
        24 => [52, 190, 88, 205, 109, 39, 176, 21, 155, 197, 251, 223, 155, 21, 5, 172, 254, 124, 12, 181, 184, 96, 50, 193],
        28 => [211, 231, 43, 97, 71, 96, 103, 174, 37, 151, 170, 53, 75, 34, 249, 121, 17, 138, 110, 213, 141, 136, 120, 151, 233, 168, 93, 255],
        36 => [245, 127, 242, 218, 130, 250, 162, 181, 102, 120, 84, 179, 220, 251, 80, 182, 229, 18, 2, 4, 68, 33, 101, 137, 95, 119, 115, 44, 175, 184, 59, 25, 225, 98, 81, 112],
        42 => [77, 193, 137, 31, 19, 38, 22, 153, 247, 105, 122, 2, 245, 133, 242, 8, 175, 95, 100, 9, 167, 105, 214, 111, 57, 121, 21, 1, 253, 57, 54, 101, 248, 202, 69, 50, 150, 177, 226, 5, 9, 5],
        48 => [245, 132, 172, 223, 96, 32, 117, 22, 238, 133, 238, 231, 205, 188, 237, 87, 191, 106, 16, 147, 118, 23, 37, 90, 170, 205, 131, 88, 120, 100, 66, 138, 186, 240, 82, 44, 176, 87, 187, 147, 160, 175, 69, 213, 92, 253, 225, 19],
        56 => [175, 9, 223, 238, 12, 17, 220, 208, 100, 29, 175, 170, 230, 192, 215, 235, 150, 159, 36, 223, 38, 200, 132, 54, 228, 146, 218, 234, 117, 203, 29, 232, 144, 238, 22, 150, 201, 117, 62, 207, 164, 13, 137, 245, 127, 67, 247, 28, 155, 43, 203, 107, 233, 53, 143, 46],
        62 => [242, 93, 169, 50, 144, 210, 39, 118, 202, 188, 201, 189, 143, 108, 196, 37, 185, 112, 134, 230, 245, 63, 197, 190, 250, 106, 185, 221, 175, 64, 114, 71, 161, 44, 147, 6, 27, 218, 51, 63, 87, 10, 40, 130, 188, 17, 163, 31, 176, 170, 4, 107, 232, 7, 94, 166, 224, 124, 86, 47, 11, 204],
        68 => [220, 228, 173, 89, 251, 149, 159, 56, 89, 33, 147, 244, 154, 36, 73, 127, 213, 136, 248, 180, 234, 197, 158, 177, 68, 122, 93, 213, 15, 160, 227, 236, 66, 139, 153, 185, 202, 167, 179, 25, 220, 232, 96, 210, 231, 136, 223, 239, 181, 241, 59, 52, 172, 25, 49, 232, 211, 189, 64, 54, 108, 153, 132, 63, 96, 103, 82, 186],
    ];

    public readonly int $symbolWidth;

    public readonly int $symbolHeight;

    /** Aliased for backward compatibility — = max(symbolWidth, symbolHeight). */
    public readonly int $size;

    public readonly int $dataCw;

    public readonly int $eccCw;

    public readonly bool $rectangular;

    /** @var list<list<bool>> */
    private array $modules;

    /**
     * Encoding mode constants.
     */
    public const MODE_AUTO = 'auto';

    public const MODE_ASCII = 'ascii';

    public const MODE_C40 = 'c40';

    public const MODE_TEXT = 'text';

    public const MODE_X12 = 'x12';

    public const MODE_EDIFACT = 'edifact';

    public const MODE_BASE256 = 'base256';

    /**
     * Macro 05/06 mode markers per ISO/IEC 16022 §5.2.4.6.
     *   Macro 05 (CW 236) — header structure "[)>RS05GS" prepended conceptually
     *   Macro 06 (CW 237) — same but "[)>RS06GS"
     * Decoder reconstructs full string on decode. Used for compact transit
     * applications: AIM TID and similar.
     */
    public const MACRO_05 = 236;

    public const MACRO_06 = 237;

    /**
     * FNC1 (Function 1) codeword (232) — GS1 DataMatrix marker.
     * Prepended to payload for GS1-compliant symbols. Per ISO/IEC 16022 §5.2.4.5.
     */
    public const FNC1 = 232;

    /**
     * ECI (Extended Channel Interpretation) marker CW 241.
     * Per ISO/IEC 16022 §5.2.4.4 — followed by 1-3 byte designator value.
     */
    public const ECI_CODEWORD = 241;

    public function __construct(
        public readonly string $data,
        bool $allowRectangular = true,
        string $mode = self::MODE_ASCII,
        ?int $macroMode = null,
        bool $gs1 = false,
        ?int $eciDesignator = null,
    ) {
        if ($data === '') {
            throw new \InvalidArgumentException('DataMatrix input must be non-empty');
        }
        if ($macroMode !== null && $macroMode !== self::MACRO_05 && $macroMode !== self::MACRO_06) {
            throw new \InvalidArgumentException('Macro mode must be MACRO_05 (236) or MACRO_06 (237)');
        }

        // 1. Encode data per chosen mode (or auto-pick best).
        $codewords = match ($mode) {
            self::MODE_ASCII => self::encodeAscii($data),
            self::MODE_C40 => self::encodeC40($data),
            self::MODE_TEXT => self::encodeText($data),
            self::MODE_X12 => self::encodeX12($data),
            self::MODE_EDIFACT => self::encodeEdifact($data),
            self::MODE_BASE256 => self::encodeBase256($data),
            self::MODE_AUTO => self::encodeAuto($data),
            default => throw new \InvalidArgumentException("Unknown mode: $mode"),
        };

        // Macro 05/06 prefix codeword (if set).
        if ($macroMode !== null) {
            array_unshift($codewords, $macroMode);
        }
        // GS1 DataMatrix — FNC1 (232) as first codeword.
        if ($gs1) {
            array_unshift($codewords, self::FNC1);
        }
        // ECI marker — codeword 241 + 1-3 byte designator.
        if ($eciDesignator !== null) {
            if ($eciDesignator < 0 || $eciDesignator > 999999) {
                throw new \InvalidArgumentException('DataMatrix ECI designator must be 0..999999');
            }
            $eciCw = [self::ECI_CODEWORD];
            if ($eciDesignator <= 126) {
                $eciCw[] = $eciDesignator + 1; // 1 byte: value + 1
            } elseif ($eciDesignator <= 16382) {
                $eciCw[] = intdiv($eciDesignator - 127, 254) + 128;
                $eciCw[] = (($eciDesignator - 127) % 254) + 1;
            } else {
                // 3-byte: complex offset arithmetic.
                $offset = $eciDesignator - 16383;
                $eciCw[] = intdiv($offset, 64516) + 192;
                $eciCw[] = (intdiv($offset, 254) % 254) + 1;
                $eciCw[] = ($offset % 254) + 1;
            }
            $codewords = array_merge($eciCw, $codewords);
        }

        // 2. Pick smallest symbol fitting codewords.
        $symbol = null;
        foreach (self::SYMBOLS as $s) {
            if (! $allowRectangular && $s[0]) {
                continue;
            }
            if (count($codewords) <= $s[1]) {
                $symbol = $s;
                break;
            }
        }
        if ($symbol === null) {
            throw new \InvalidArgumentException(sprintf(
                'DataMatrix capacity exceeded (%d codewords; max %d)',
                count($codewords), self::SYMBOLS[count(self::SYMBOLS) - 1][1],
            ));
        }

        $rect = $symbol[0];
        $dataCap = $symbol[1];
        $eccCap = $symbol[2];
        $mW = $symbol[3];
        $mH = $symbol[4];
        $regions = $symbol[5];
        $rsData = $symbol[6];
        $rsEcc = $symbol[7];
        // Optional explicit block count (default = intdiv).
        $blockCountOverride = $symbol[8] ?? null;
        $hRegions = self::horizontalRegions($regions);
        $vRegions = self::verticalRegions($regions);
        $this->symbolWidth = $hRegions * $mW + 2 * $hRegions;
        $this->symbolHeight = $vRegions * $mH + 2 * $vRegions;
        $this->size = max($this->symbolWidth, $this->symbolHeight);
        $this->dataCw = $dataCap;
        $this->eccCw = $eccCap;
        $this->rectangular = $rect;

        // 3. Pad data to full capacity.
        $codewords = self::padCodewords($codewords, $dataCap);

        // 4. Compute ECC (interleaved for blockCount > 1).
        $blockCount = $blockCountOverride ?? intdiv($dataCap, $rsData);
        $allCw = $blockCount === 1
            ? array_merge($codewords, self::createEccBlock($codewords, $eccCap))
            : self::interleavedEcc($codewords, $dataCap, $eccCap, $blockCount, $rsEcc);

        // 5. Place codewords in data grid (DefaultPlacement algorithm).
        $dataWidth = $hRegions * $mW;
        $dataHeight = $vRegions * $mH;
        $grid = self::placeBits($allCw, $dataWidth, $dataHeight);

        // 6. Assemble final symbol: split grid into regions + add finder/timing.
        $this->modules = self::assembleSymbol($grid, $hRegions, $vRegions, $mW, $mH);
    }

    public function size(): int
    {
        return $this->size;
    }

    public function symbolWidth(): int
    {
        return $this->symbolWidth;
    }

    public function symbolHeight(): int
    {
        return $this->symbolHeight;
    }

    /** @return list<list<bool>> */
    public function modules(): array
    {
        return $this->modules;
    }

    // ─── ASCII encoding ───────────────────────────────────────────────────

    /**
     * @return list<int>
     */
    public static function encodeAscii(string $data): array
    {
        $cw = [];
        $len = strlen($data);
        $i = 0;
        while ($i < $len) {
            $b = ord($data[$i]);
            // Digit pair compression.
            if ($i + 1 < $len && ctype_digit($data[$i]) && ctype_digit($data[$i + 1])) {
                $val = 10 * (ord($data[$i]) - 0x30) + (ord($data[$i + 1]) - 0x30);
                $cw[] = 130 + $val;
                $i += 2;
            } elseif ($b <= 127) {
                $cw[] = $b + 1;
                $i++;
            } else {
                // Bytes 128..255: use Upper Shift (codeword 235) + (byte - 128) + 1.
                $cw[] = 235;
                $cw[] = ($b - 128) + 1;
                $i++;
            }
        }

        return $cw;
    }

    /**
     * Auto-mode encoder — picks best encoding per input characteristics.
     *  - Pure digits → ASCII (digit-pair compression built in, 2 chars / 1 CW)
     *  - High proportion uppercase+digits → C40 (3 chars / 2 CW)
     *  - High proportion lowercase → Text (3 chars / 2 CW)
     *  - Non-ASCII bytes (>127) → Base 256 (1 byte / 1 CW + randomization)
     *  - Default → ASCII (safe for mixed content)
     *
     * @return list<int>
     */
    public static function encodeAuto(string $data): array
    {
        // Non-ASCII presence → Base 256 (only mode handling binary).
        for ($i = 0; $i < strlen($data); $i++) {
            if (ord($data[$i]) > 127) {
                return self::encodeBase256($data);
            }
        }
        // For pure-digit input, ASCII digit-pair compression equals C40 (both 2:1).
        // For uppercase-heavy → C40; lowercase-heavy → Text. Default ASCII.
        $countUpper = 0;
        $countLower = 0;
        $countDigit = 0;
        $n = strlen($data);
        for ($i = 0; $i < $n; $i++) {
            $b = ord($data[$i]);
            if ($b >= 65 && $b <= 90) {
                $countUpper++;
            } elseif ($b >= 97 && $b <= 122) {
                $countLower++;
            } elseif ($b >= 48 && $b <= 57) {
                $countDigit++;
            }
        }
        // Heuristic: long uppercase+digit run → C40 if C40-eligible chars dominate.
        if ($n >= 6 && ($countUpper + $countDigit) >= 0.7 * $n && self::isC40Eligible($data)) {
            return self::encodeC40($data);
        }
        if ($n >= 6 && $countLower >= 0.5 * $n && self::isTextEligible($data)) {
            return self::encodeText($data);
        }

        return self::encodeAscii($data);
    }

    /**
     * Base 256 encoding mode per ISO/IEC 16022 §5.2.4.7.
     * Length-prefixed binary data with 255-state randomization.
     *
     * Codeword stream:
     *  - 231 (switch to Base 256)
     *  - Length: 1 byte (1..249) or 2 bytes (high = (L-249)/250 + 249, low = L%250)
     *  - Data bytes randomized: (byte + (149*pos)%255 + 1) mod 256
     *    Where pos = 1-based position of this codeword in total stream.
     *
     * @return list<int>
     */
    public static function encodeBase256(string $data): array
    {
        $cw = [231]; // switch to Base 256
        $len = strlen($data);
        // Length field. 2-byte encoding for long payloads.
        if ($len <= 249) {
            $cw[] = $len;
        } elseif ($len <= 1555) {
            $cw[] = intdiv($len, 250) + 249;
            $cw[] = $len % 250;
        } else {
            throw new \InvalidArgumentException(sprintf(
                'DataMatrix Base 256 max payload 1555 bytes; got %d', $len
            ));
        }
        // Data bytes — 255-state randomization. pos = 1-based codeword position
        // of THIS data byte in overall stream (after 231 + length bytes).
        $headerLen = count($cw);
        for ($i = 0; $i < $len; $i++) {
            $pos = $headerLen + $i + 1; // 1-based position
            $pseudoRandom = (149 * $pos) % 255 + 1;
            $temp = ord($data[$i]) + $pseudoRandom;
            $cw[] = $temp <= 255 ? $temp : $temp - 256;
        }
        // Also randomize the length byte(s) themselves.
        // Length bytes get same randomization treatment as data bytes
        // (with pos = their position in codeword stream).
        // Re-randomize header.
        for ($i = 1; $i < $headerLen; $i++) {
            $pos = $i + 1; // 1-based
            $pseudoRandom = (149 * $pos) % 255 + 1;
            $temp = $cw[$i] + $pseudoRandom;
            $cw[$i] = $temp <= 255 ? $temp : $temp - 256;
        }

        return $cw;
    }

    /**
     * C40 encoding mode per ISO/IEC 16022 §5.2.5.
     * 3 characters packed into 2 codewords (16 bits). Primary alphabet:
     * uppercase + digits + space + shift tables for lowercase / punctuation.
     *
     * @return list<int>
     */
    public static function encodeC40(string $data): array
    {
        return self::encodeC40OrText($data, latchSwitch: 230, useTextSet: false);
    }

    /**
     * Text encoding mode — like C40 but primary alphabet lowercase.
     *
     * @return list<int>
     */
    public static function encodeText(string $data): array
    {
        return self::encodeC40OrText($data, latchSwitch: 239, useTextSet: true);
    }

    /**
     * X12 encoding mode per ISO/IEC 16022 §5.2.7.
     * Subset of C40 alphabet (uppercase + digits + space + < > * + - . / : =).
     * 3 chars → 2 codewords. Switch codeword 238.
     *
     * @return list<int>
     */
    public static function encodeX12(string $data): array
    {
        $cw = [238];
        $values = [];
        $n = strlen($data);
        for ($i = 0; $i < $n; $i++) {
            $values[] = self::x12CharValue($data[$i]);
        }
        $cw = array_merge($cw, self::packTriplets($values));
        // Latch back to ASCII if data ended on incomplete triplet (per spec
        // ASCII unlatch codeword 254 added).
        if (count($values) % 3 !== 0) {
            $cw[] = 254;
        }

        return $cw;
    }

    /**
     * EDIFACT encoding mode per ISO/IEC 16022 §5.2.8.
     * 4 chars (each 6-bit, ASCII 32..94 range) packed into 3 codewords.
     * Switch codeword 240. Terminator 011111 (binary), unlatch to ASCII.
     *
     * @return list<int>
     */
    public static function encodeEdifact(string $data): array
    {
        $cw = [240];
        $bits = '';
        $n = strlen($data);
        for ($i = 0; $i < $n; $i++) {
            $b = ord($data[$i]);
            if ($b < 32 || $b > 94) {
                throw new \InvalidArgumentException(sprintf(
                    'EDIFACT supports ASCII 32..94 only; got 0x%02X', $b
                ));
            }
            // 6-bit encoding: bytes 32..63 → values 32..63; bytes 64..94 → 0..30.
            $val = $b & 0x3F;
            $bits .= str_pad(decbin($val), 6, '0', STR_PAD_LEFT);
        }
        // Append unlatch terminator (6 bits "011111" = 0x1F) if data
        // or space remaining. Per spec: if data ends on multiple-of-3-cw
        // boundary, no unlatch needed. Else pad with zero bits after unlatch.
        $bits .= '011111';
        // Pad to multiple of 8 bits (= multiple of 4 chars or 3 codewords).
        while (strlen($bits) % 8 !== 0) {
            $bits .= '0';
        }
        // Convert byte-by-byte → codewords.
        for ($i = 0; $i < strlen($bits); $i += 8) {
            $cw[] = bindec(substr($bits, $i, 8));
        }

        return $cw;
    }

    private static function isC40Eligible(string $data): bool
    {
        for ($i = 0; $i < strlen($data); $i++) {
            $b = ord($data[$i]);
            // C40 basic set + uppercase + digits + space; non-basic chars handled
            // via shift but inefficient. Accept primarily uppercase+digits+space.
            if (! (($b >= 65 && $b <= 90) || ($b >= 48 && $b <= 57) || $b === 32)) {
                return false;
            }
        }

        return true;
    }

    private static function isTextEligible(string $data): bool
    {
        for ($i = 0; $i < strlen($data); $i++) {
            $b = ord($data[$i]);
            if (! (($b >= 97 && $b <= 122) || ($b >= 48 && $b <= 57) || $b === 32)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<int>
     */
    private static function encodeC40OrText(string $data, int $latchSwitch, bool $useTextSet): array
    {
        $cw = [$latchSwitch];
        $values = [];
        $n = strlen($data);
        for ($i = 0; $i < $n; $i++) {
            $values = array_merge($values, self::c40CharValues($data[$i], $useTextSet));
        }
        $cw = array_merge($cw, self::packTriplets($values));
        // Unlatch to ASCII (codeword 254) if data ended on incomplete triplet
        // (codeword 254 also serves as end-of-mode signal).
        if (count($values) % 3 !== 0) {
            $cw[] = 254;
        }

        return $cw;
    }

    /**
     * Pack 3-value triplets into 2 codewords each: V1*1600 + V2*40 + V3 + 1.
     * Top byte = high, bottom byte = low.
     *
     * @param  list<int>  $values
     * @return list<int>
     */
    private static function packTriplets(array $values): array
    {
        // Pad partial triplet with 0 (shift 1 in C40/Text — for SHIFT_1, value 0).
        // X12 spec uses different padding; for simplicity pad with 0.
        while (count($values) % 3 !== 0) {
            $values[] = 0;
        }
        $cw = [];
        for ($i = 0; $i < count($values); $i += 3) {
            $packed = 1600 * $values[$i] + 40 * $values[$i + 1] + $values[$i + 2] + 1;
            $cw[] = ($packed >> 8) & 0xFF;
            $cw[] = $packed & 0xFF;
        }

        return $cw;
    }

    /**
     * C40/Text char → value list (most chars 1 value; shifted chars 2 values).
     *
     * @return list<int>
     */
    private static function c40CharValues(string $ch, bool $useTextSet): array
    {
        $b = ord($ch);
        // Basic set: SHIFT 1/2/3 ChimeSet or direct value.
        if ($b === 32) {
            return [3]; // space
        }
        if ($b >= 48 && $b <= 57) {
            return [$b - 48 + 4]; // digits → 4..13
        }
        if ($useTextSet) {
            // Text set: primary alphabet lowercase a-z → values 14..39.
            if ($b >= 97 && $b <= 122) {
                return [$b - 97 + 14];
            }
            // Uppercase A-Z → shift 3 + value (b-65). Shift 3 = value 2.
            if ($b >= 65 && $b <= 90) {
                return [2, $b - 65 + 14];
            }
        } else {
            // C40 set: primary uppercase A-Z → values 14..39.
            if ($b >= 65 && $b <= 90) {
                return [$b - 65 + 14];
            }
            // Lowercase a-z → shift 3 + value (b-97).
            if ($b >= 97 && $b <= 122) {
                return [2, $b - 97 + 14];
            }
        }
        // Punctuation / control: SHIFT 1 for 0..31 (value 0 + b), SHIFT 2 for 33..47/58..64/91..95 etc.
        if ($b <= 31) {
            return [0, $b];
        }
        if ($b >= 33 && $b <= 47) {
            return [1, $b - 33];
        }
        if ($b >= 58 && $b <= 64) {
            return [1, $b - 58 + 15];
        }
        if ($b >= 91 && $b <= 95) {
            return [1, $b - 91 + 22];
        }
        // Bytes > 127: upper shift + SHIFT2 + (b - 128).
        if ($b >= 128) {
            return [1, 30, ...self::c40CharValues(chr($b - 128), $useTextSet)];
        }
        throw new \InvalidArgumentException(sprintf('C40/Text does not cover byte 0x%02X', $b));
    }

    private static function x12CharValue(string $ch): int
    {
        $b = ord($ch);

        return match (true) {
            $b === 0x0D => 0,           // CR
            $b === 0x2A => 1,           // *
            $b === 0x3E => 2,           // >
            $b === 0x20 => 3,           // space
            $b >= 0x30 && $b <= 0x39 => $b - 0x30 + 4,    // 0..9 → 4..13
            $b >= 0x41 && $b <= 0x5A => $b - 0x41 + 14,   // A..Z → 14..39
            default => throw new \InvalidArgumentException(sprintf(
                'X12 supports only CR/*/>/SPACE/0-9/A-Z; got 0x%02X', $b
            )),
        };
    }

    /**
     * Pad codewords to capacity per ISO/IEC 16022 §5.2.4.1:
     *  - First pad = 129 (terminator).
     *  - Subsequent pads: (149 * pos) % 253 + 1, added to 129, mod 254.
     *
     * @param  list<int>  $cw
     * @return list<int>
     */
    public static function padCodewords(array $cw, int $capacity): array
    {
        if (count($cw) >= $capacity) {
            return array_slice($cw, 0, $capacity);
        }
        $cw[] = 129;
        while (count($cw) < $capacity) {
            $pos = count($cw) + 1;
            $rnd = (149 * $pos) % 253 + 1;
            $cw[] = (129 + $rnd) % 254;
        }

        return $cw;
    }

    // ─── Reed-Solomon over GF(256), primitive 0x12D ───────────────────────

    /** @var list<int> */
    private static array $log;

    /** @var list<int> */
    private static array $alog;

    private static function initGf(): void
    {
        if (isset(self::$log)) {
            return;
        }
        $log = array_fill(0, 256, 0);
        $alog = array_fill(0, 255, 0);
        $p = 1;
        for ($i = 0; $i < 255; $i++) {
            $alog[$i] = $p;
            $log[$p] = $i;
            $p <<= 1;
            if ($p >= 256) {
                $p ^= 0x12D;
            }
        }
        self::$log = $log;
        self::$alog = $alog;
    }

    /**
     * Encode RS ECC block.
     * Output order: reversed (ECC[0] becomes ECC[n-1] etc).
     *
     * @param  list<int>  $cw
     * @return list<int>
     */
    public static function createEccBlock(array $cw, int $eccLen): array
    {
        self::initGf();
        if (! isset(self::FACTORS[$eccLen])) {
            throw new \InvalidArgumentException("Unsupported ECC length: $eccLen");
        }
        $poly = self::FACTORS[$eccLen];
        $ecc = array_fill(0, $eccLen, 0);
        foreach ($cw as $codeword) {
            $m = $ecc[$eccLen - 1] ^ $codeword;
            for ($k = $eccLen - 1; $k > 0; $k--) {
                if ($m !== 0 && $poly[$k] !== 0) {
                    $ecc[$k] = $ecc[$k - 1] ^ self::$alog[(self::$log[$m] + self::$log[$poly[$k]]) % 255];
                } else {
                    $ecc[$k] = $ecc[$k - 1];
                }
            }
            if ($m !== 0 && $poly[0] !== 0) {
                $ecc[0] = self::$alog[(self::$log[$m] + self::$log[$poly[0]]) % 255];
            } else {
                $ecc[0] = 0;
            }
        }
        // Reverse.
        return array_reverse($ecc);
    }

    /**
     * Interleaved RS encoding for symbols with multiple RS blocks.
     *
     * Per ISO/IEC 16022 ECC200:
     *  - Data codewords interleaved by blockCount: block 0 takes positions
     *    0, blockCount, 2*blockCount, ...; block 1 takes positions 1, 1+bc, ...
     *  - Each block's ECC computed separately, then interleaved similarly
     *    into the ECC tail.
     *
     * @param  list<int>  $data
     * @return list<int>  data + ECC, length dataCap + eccCap
     */
    public static function interleavedEcc(array $data, int $dataCap, int $eccCap, int $blockCount, int $rsBlockError): array
    {
        $result = array_merge($data, array_fill(0, $eccCap, 0));
        for ($block = 0; $block < $blockCount; $block++) {
            $blockData = [];
            for ($d = $block; $d < $dataCap; $d += $blockCount) {
                $blockData[] = $data[$d];
            }
            $blockEcc = self::createEccBlock($blockData, $rsBlockError);
            $pos = 0;
            for ($e = $block; $e < $rsBlockError * $blockCount; $e += $blockCount) {
                $result[$dataCap + $e] = $blockEcc[$pos++];
            }
        }

        return $result;
    }

    // ─── DefaultPlacement ─────────────────────────────────────────────────

    /**
     * Place codeword bits into data grid per DefaultPlacement algorithm.
     *
     * @param  list<int>  $codewords  all data + ECC codewords
     * @return list<list<bool>>  grid[row][col]
     */
    public static function placeBits(array $codewords, int $numCols, int $numRows): array
    {
        // Use null-initialized grid; setBit fills with bool.
        $bits = array_fill(0, $numRows * $numCols, null);

        $pos = 0;
        $row = 4;
        $col = 0;
        do {
            // 4 corner cases.
            if ($row === $numRows && $col === 0) {
                self::corner1($bits, $numCols, $numRows, $codewords[$pos++]);
            }
            if ($row === $numRows - 2 && $col === 0 && $numCols % 4 !== 0) {
                self::corner2($bits, $numCols, $numRows, $codewords[$pos++]);
            }
            if ($row === $numRows - 2 && $col === 0 && $numCols % 8 === 4) {
                self::corner3($bits, $numCols, $numRows, $codewords[$pos++]);
            }
            if ($row === $numRows + 4 && $col === 2 && $numCols % 8 === 0) {
                self::corner4($bits, $numCols, $numRows, $codewords[$pos++]);
            }
            // Sweep upward-right (Walter direction 1).
            do {
                if ($row < $numRows && $col >= 0 && $bits[$row * $numCols + $col] === null) {
                    self::utah($bits, $numCols, $numRows, $row, $col, $codewords[$pos++]);
                }
                $row -= 2;
                $col += 2;
            } while ($row >= 0 && $col < $numCols);
            $row++;
            $col += 3;
            // Sweep downward-left (Walter direction 2).
            do {
                if ($row >= 0 && $col < $numCols && $bits[$row * $numCols + $col] === null) {
                    self::utah($bits, $numCols, $numRows, $row, $col, $codewords[$pos++]);
                }
                $row += 2;
                $col -= 2;
            } while ($row < $numRows && $col >= 0);
            $row += 3;
            $col++;
        } while ($row < $numRows || $col < $numCols);

        // Lastly, if lower-right corner untouched, fill fixed pattern.
        if ($bits[($numRows - 1) * $numCols + ($numCols - 1)] === null) {
            $bits[($numRows - 1) * $numCols + ($numCols - 1)] = true;
            $bits[($numRows - 2) * $numCols + ($numCols - 2)] = true;
        }

        // Convert flat null/bool array → 2D bool grid.
        $grid = [];
        for ($r = 0; $r < $numRows; $r++) {
            $rowArr = [];
            for ($c = 0; $c < $numCols; $c++) {
                $rowArr[] = (bool) ($bits[$r * $numCols + $c] ?? false);
            }
            $grid[] = $rowArr;
        }

        return $grid;
    }

    /**
     * Place 1 bit at (col, row) wrapping negative indices per Walter rules.
     *
     * @param  array<int, bool|null>  $bits
     */
    private static function module(array &$bits, int $numCols, int $numRows, int $row, int $col, int $pos, int $bit, int $cw): void
    {
        if ($row < 0) {
            $row += $numRows;
            $col += 4 - (($numRows + 4) % 8);
        }
        if ($col < 0) {
            $col += $numCols;
            $row += 4 - (($numCols + 4) % 8);
        }
        $v = $cw & (1 << (8 - $bit));
        $bits[$row * $numCols + $col] = ($v !== 0);
    }

    /**
     * @param  array<int, bool|null>  $bits
     */
    private static function utah(array &$bits, int $numCols, int $numRows, int $row, int $col, int $cw): void
    {
        self::module($bits, $numCols, $numRows, $row - 2, $col - 2, 0, 1, $cw);
        self::module($bits, $numCols, $numRows, $row - 2, $col - 1, 0, 2, $cw);
        self::module($bits, $numCols, $numRows, $row - 1, $col - 2, 0, 3, $cw);
        self::module($bits, $numCols, $numRows, $row - 1, $col - 1, 0, 4, $cw);
        self::module($bits, $numCols, $numRows, $row - 1, $col,     0, 5, $cw);
        self::module($bits, $numCols, $numRows, $row,     $col - 2, 0, 6, $cw);
        self::module($bits, $numCols, $numRows, $row,     $col - 1, 0, 7, $cw);
        self::module($bits, $numCols, $numRows, $row,     $col,     0, 8, $cw);
    }

    /** @param array<int, bool|null> $bits */
    private static function corner1(array &$bits, int $numCols, int $numRows, int $cw): void
    {
        self::module($bits, $numCols, $numRows, $numRows - 1, 0, 0, 1, $cw);
        self::module($bits, $numCols, $numRows, $numRows - 1, 1, 0, 2, $cw);
        self::module($bits, $numCols, $numRows, $numRows - 1, 2, 0, 3, $cw);
        self::module($bits, $numCols, $numRows, 0, $numCols - 2, 0, 4, $cw);
        self::module($bits, $numCols, $numRows, 0, $numCols - 1, 0, 5, $cw);
        self::module($bits, $numCols, $numRows, 1, $numCols - 1, 0, 6, $cw);
        self::module($bits, $numCols, $numRows, 2, $numCols - 1, 0, 7, $cw);
        self::module($bits, $numCols, $numRows, 3, $numCols - 1, 0, 8, $cw);
    }

    /** @param array<int, bool|null> $bits */
    private static function corner2(array &$bits, int $numCols, int $numRows, int $cw): void
    {
        self::module($bits, $numCols, $numRows, $numRows - 3, 0, 0, 1, $cw);
        self::module($bits, $numCols, $numRows, $numRows - 2, 0, 0, 2, $cw);
        self::module($bits, $numCols, $numRows, $numRows - 1, 0, 0, 3, $cw);
        self::module($bits, $numCols, $numRows, 0, $numCols - 4, 0, 4, $cw);
        self::module($bits, $numCols, $numRows, 0, $numCols - 3, 0, 5, $cw);
        self::module($bits, $numCols, $numRows, 0, $numCols - 2, 0, 6, $cw);
        self::module($bits, $numCols, $numRows, 0, $numCols - 1, 0, 7, $cw);
        self::module($bits, $numCols, $numRows, 1, $numCols - 1, 0, 8, $cw);
    }

    /** @param array<int, bool|null> $bits */
    private static function corner3(array &$bits, int $numCols, int $numRows, int $cw): void
    {
        self::module($bits, $numCols, $numRows, $numRows - 3, 0, 0, 1, $cw);
        self::module($bits, $numCols, $numRows, $numRows - 2, 0, 0, 2, $cw);
        self::module($bits, $numCols, $numRows, $numRows - 1, 0, 0, 3, $cw);
        self::module($bits, $numCols, $numRows, 0, $numCols - 2, 0, 4, $cw);
        self::module($bits, $numCols, $numRows, 0, $numCols - 1, 0, 5, $cw);
        self::module($bits, $numCols, $numRows, 1, $numCols - 1, 0, 6, $cw);
        self::module($bits, $numCols, $numRows, 2, $numCols - 1, 0, 7, $cw);
        self::module($bits, $numCols, $numRows, 3, $numCols - 1, 0, 8, $cw);
    }

    /** @param array<int, bool|null> $bits */
    private static function corner4(array &$bits, int $numCols, int $numRows, int $cw): void
    {
        self::module($bits, $numCols, $numRows, $numRows - 1, 0,           0, 1, $cw);
        self::module($bits, $numCols, $numRows, $numRows - 1, $numCols - 1, 0, 2, $cw);
        self::module($bits, $numCols, $numRows, 0, $numCols - 3, 0, 3, $cw);
        self::module($bits, $numCols, $numRows, 0, $numCols - 2, 0, 4, $cw);
        self::module($bits, $numCols, $numRows, 0, $numCols - 1, 0, 5, $cw);
        self::module($bits, $numCols, $numRows, 1, $numCols - 3, 0, 6, $cw);
        self::module($bits, $numCols, $numRows, 1, $numCols - 2, 0, 7, $cw);
        self::module($bits, $numCols, $numRows, 1, $numCols - 1, 0, 8, $cw);
    }

    // ─── Symbol assembly ──────────────────────────────────────────────────

    /**
     * Assemble final symbol matrix from data grid by splitting into
     * regions and adding finder/timing patterns per region.
     *
     * Each region tile (mW+2) × (mH+2) total:
     *  - Top edge: alternating timing pattern (B/W/B/W...)
     *  - Right edge: alternating timing
     *  - Bottom edge: solid black (L-finder)
     *  - Left edge: solid black (L-finder)
     *  - Inner (mW × mH): data from placement grid
     *
     * @param  list<list<bool>>  $grid
     * @return list<list<bool>>
     */
    private static function assembleSymbol(array $grid, int $hRegions, int $vRegions, int $mW, int $mH): array
    {
        $totalW = $hRegions * $mW + 2 * $hRegions;
        $totalH = $vRegions * $mH + 2 * $vRegions;
        $sym = array_fill(0, $totalH, array_fill(0, $totalW, false));

        for ($vr = 0; $vr < $vRegions; $vr++) {
            for ($hr = 0; $hr < $hRegions; $hr++) {
                // Region origin in final symbol.
                $rTop = $vr * ($mH + 2);
                $rLeft = $hr * ($mW + 2);
                // Source grid origin.
                $gTop = $vr * $mH;
                $gLeft = $hr * $mW;

                // 1. Timing FIRST (will be overridden at corners by L-finder).
                // Top edge: B-W-B-W-... starting B at col 0 (so corner with
                // L-finder left = B).
                for ($j = 0; $j < $mW + 2; $j++) {
                    $sym[$rTop][$rLeft + $j] = ($j % 2 === 0);
                }
                // Right edge: W-B-W-B-... starting W at row 0 (so corner with
                // L-finder bottom = B on row mH+1).
                for ($i = 0; $i < $mH + 2; $i++) {
                    $sym[$rTop + $i][$rLeft + $mW + 1] = ($i % 2 === 1);
                }
                // 2. L-finder SECOND (overrides timing at corners).
                for ($i = 0; $i < $mH + 2; $i++) {
                    $sym[$rTop + $i][$rLeft] = true;            // left edge solid
                }
                for ($j = 0; $j < $mW + 2; $j++) {
                    $sym[$rTop + $mH + 1][$rLeft + $j] = true;  // bottom edge solid
                }
                // 3. Inner data area.
                for ($r = 0; $r < $mH; $r++) {
                    for ($c = 0; $c < $mW; $c++) {
                        $sym[$rTop + 1 + $r][$rLeft + 1 + $c] = $grid[$gTop + $r][$gLeft + $c];
                    }
                }
            }
        }

        return $sym;
    }

    private static function horizontalRegions(int $dataRegions): int
    {
        return match ($dataRegions) {
            1 => 1,
            2, 4 => 2,
            16 => 4,
            36 => 6,
            default => throw new \InvalidArgumentException("Unsupported regions: $dataRegions"),
        };
    }

    private static function verticalRegions(int $dataRegions): int
    {
        return match ($dataRegions) {
            1, 2 => 1,
            4 => 2,
            16 => 4,
            36 => 6,
            default => throw new \InvalidArgumentException("Unsupported regions: $dataRegions"),
        };
    }
}
