<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * Code 128 linear barcode encoder.
 *
 * ISO/IEC 15417. Implements Code 128 Sets A, B, and C with auto-mode
 * switching and GS1-128 (Application Identifiers) support.
 *  - Set A: control chars 00..31, FNC functions.
 *  - Set B: printable ASCII 32..126 (alphanumeric + punctuation).
 *  - Set C: numeric pairs (2× compression for digit-only).
 *
 * Code 128 structure:
 *
 *   [Start B] [data chars...] [checksum] [Stop]
 *
 * Each character = 11 modules (3 bars + 3 spaces). Bars alternate
 * starting with black. Stop pattern has 13 modules.
 *
 * Usage:
 *   $enc = new Code128Encoder('ABC-123');
 *   $modules = $enc->modules();      // list of bool: true = black
 *   $width = $enc->moduleCount();    // total module width
 */
final class Code128Encoder
{
    /**
     * Code 128 patterns: index = code value (0..106), value = 6-digit
     * string describing module widths: bar/space/bar/space/bar/space.
     *
     * Example: value 0 "212222" = bar-2, space-1, bar-2, space-2, bar-2,
     * space-2. Total 11 modules.
     *
     * Index 106 = Stop+Termination bar (2+3+1+1+1+2+3 = 13 modules).
     */
    private const PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312',
        '132212', '221213', '221312', '231212', '112232', '122132', '122231', '113222',
        '123122', '123221', '223211', '221132', '221231', '213212', '223112', '312131',
        '311222', '321122', '321221', '312212', '322112', '322211', '212123', '212321',
        '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121',
        '313121', '211331', '231131', '213113', '213311', '213131', '311123', '311321',
        '331121', '312113', '312311', '332111', '314111', '221411', '431111', '111224',
        '111422', '121124', '121421', '141122', '141221', '112214', '112412', '122114',
        '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112',
        '421211', '212141', '214121', '412121', '111143', '111341', '131141', '114113',
        '114311', '411113', '411311', '113141', '114131', '311141', '411131', '211412',
        '211214', '211232',
        // Stop pattern (index 106) — 13 modules: 2 1 1 4 1 3 1 (7 entries).
        '2331112',
    ];

    private const START_A = 103;

    private const START_B = 104;

    private const START_C = 105;

    private const STOP = 106;

    // Code-switch / shift codewords. Different code values per set.


    private const CODE_C_FROM_A = 99;   // in Set A: CODE_C permanent switch


    private const CODE_B_FROM_A = 100;  // in Set A: CODE_B

    private const CODE_B_FROM_C = 100;  // in Set C: CODE_B

    private const CODE_A_FROM_B = 101;  // in Set B: CODE_A


    private const FNC1 = 102;           // Function 1 (GS1-128 separator, all sets)

    /**
     * AI lengths for GS1-128. Variable-length AIs need FNC1 after.
     * Key = AI code (2-4 digits), value = data length (null = variable).
     *
     * @var array<string, ?int>
     */
    private const GS1_AI_LENGTHS = [
        '00' => 18, '01' => 14, '02' => 14,
        '11' => 6, '12' => 6, '13' => 6, '15' => 6, '16' => 6, '17' => 6,
        '20' => 2,
        '422' => 3, '424' => 3, '425' => 3, '426' => 3,
        // Fixed 6-digit + 1 length indicator (treated as 6).
        '310' => 7, '311' => 7, '312' => 7, '313' => 7, '314' => 7, '315' => 7, '316' => 7,
        '320' => 7, '321' => 7, '322' => 7, '323' => 7, '324' => 7, '325' => 7,
        '330' => 7, '331' => 7, '332' => 7, '333' => 7, '334' => 7, '335' => 7,
        '340' => 7, '341' => 7, '342' => 7, '343' => 7, '344' => 7, '345' => 7,
        '350' => 7, '351' => 7, '352' => 7, '353' => 7, '354' => 7, '355' => 7,
        '360' => 7, '361' => 7, '362' => 7, '363' => 7, '364' => 7, '365' => 7,
        // All other AIs default to variable (null lookup).
    ];


    /**
     * Resulting modules: each entry true = black, false = white.
     *
     * @var list<bool>
     */
    private array $modules = [];

    /**
     * GS1-128 factory — parses "(NN)data(MM)data" syntax, inserts FNC1
     * codewords per GS1-128 spec.
     *
     * Variable-length AIs require FNC1 separator after data (except last AI).
     * Fixed-length AIs (per GS1 General Specifications table) have no separator.
     *
     * Common AIs supported (fixed-length subset):
     *  - 00: SSCC (18 digits)
     *  - 01: GTIN-14 (14 digits)
     *  - 02: GTIN of contained trade items (14 digits)
     *  - 11..17: Date fields (6 digits YYMMDD)
     *  - 20: Variant (2 digits)
     *  - 31xx..36xx: Measurement (6 digits + length indicator)
     *
     * All other AIs treated as variable-length → FNC1 separator emitted.
     */
    public static function gs1(string $aiData): self
    {
        return new self($aiData, autoMode: true, gs1Mode: true);
    }

    public function __construct(
        public readonly string $data,
        bool $autoMode = true,
        bool $gs1Mode = false,
    ) {
        if ($data === '') {
            throw new \InvalidArgumentException('Code 128 input must be non-empty');
        }

        if ($gs1Mode) {
            $codes = $this->encodeGs1($data);
            $this->finalize($codes);

            return;
        }
        // Auto-mode switching — attempts optimal encoding with CODE_X
        // switches between sets to minimize total codewords.
        if ($autoMode) {
            $codes = $this->encodeAuto($data);
            $this->finalize($codes);

            return;
        }
        // Legacy mode (for backward compat / explicit set selection):
        // Set A used when input contains control chars AND no lowercase.
        // Digit-only ≥4 even → Set C.
        // Else → Set B (default).
        $hasControlChars = false;
        $hasLowercase = false;
        for ($i = 0; $i < strlen($data); $i++) {
            $b = ord($data[$i]);
            if ($b < 32) {
                $hasControlChars = true;
            } elseif ($b >= 97 && $b <= 122) {
                $hasLowercase = true;
            }
        }
        if ($hasControlChars && ! $hasLowercase) {
            $this->encodeSetA($data);
        } elseif (preg_match('@^\d+$@', $data) && strlen($data) >= 4 && strlen($data) % 2 === 0) {
            $this->encodeSetC($data);
        } else {
            $this->encodeSetB($data);
        }
    }

    /**
     * GS1-128 encoding with FNC1 separators.
     *
     * Input format: "(01)09506000134352(10)ABC123(17)260930"
     * Parser strips parens, accumulates AI + data segments, inserts FNC1
     * codeword (102) after variable-length AIs (per GS1 spec).
     *
     * Output codewords:
     *   START + FNC1 (GS1-128 indicator) + AI1+data1 + [FNC1] + AI2+data2 + ...
     *
     * Encoding outside FNC1 uses standard auto-mode logic per concatenated
     * (AI+data) chunks.
     *
     * @return list<int>
     */
    private function encodeGs1(string $aiData): array
    {
        $segments = self::parseGs1Ais($aiData);
        if ($segments === []) {
            throw new \InvalidArgumentException('GS1-128 input must contain at least one (AI)data segment');
        }
        // Build flat plaintext with FNC1 sentinel \x1D between variable-length AI boundaries.
        // \x1D = ASCII GS (Group Separator) — convenient internal marker.
        $flat = '';
        $fnc1Positions = []; // byte-positions in $flat (after flat construction)
        foreach ($segments as $i => $seg) {
            $combined = $seg['ai'].$seg['data'];
            // Validate AI length if known.
            $expectedDataLen = self::GS1_AI_LENGTHS[$seg['ai']] ?? null;
            if ($expectedDataLen !== null && strlen($seg['data']) !== $expectedDataLen) {
                throw new \InvalidArgumentException(sprintf(
                    'GS1 AI (%s) expects %d data digits, got %d ("%s")',
                    $seg['ai'], $expectedDataLen, strlen($seg['data']), $seg['data']
                ));
            }
            $flat .= $combined;
            $isVariable = $expectedDataLen === null;
            $isLast = $i === count($segments) - 1;
            if ($isVariable && ! $isLast) {
                $fnc1Positions[strlen($flat)] = true; // FNC1 inserted BEFORE next AI
            }
        }
        // Now build codewords. Start with appropriate set + FNC1 indicator.
        $startsWithDigit = ctype_digit(substr($flat, 0, 1));
        if ($startsWithDigit && strlen($flat) >= 2 && ctype_digit($flat[1])) {
            $codes = [self::START_C, self::FNC1];
            $currentSet = 'C';
        } else {
            $codes = [self::START_B, self::FNC1];
            $currentSet = 'B';
        }
        // Encode $flat using auto-mode style splitting, inserting FNC1 at
        // marked positions.
        $i = 0;
        $n = strlen($flat);
        while ($i < $n) {
            if (isset($fnc1Positions[$i])) {
                $codes[] = self::FNC1;
            }
            // Greedy: if we're in C and have 2 digits → encode pair.
            if ($currentSet === 'C' && $i + 1 < $n && ctype_digit($flat[$i]) && ctype_digit($flat[$i + 1])) {
                // Avoid pair if next byte is FNC1 sentinel position (would split pair).
                if (! isset($fnc1Positions[$i + 1])) {
                    $codes[] = (int) substr($flat, $i, 2);
                    $i += 2;

                    continue;
                }
            }
            // Need switch?
            $remainingDigits = self::countLeadingDigits($flat, $i, $fnc1Positions);
            if ($currentSet !== 'C' && $remainingDigits >= 4 && $remainingDigits % 2 === 0) {
                $codes[] = self::CODE_C_FROM_A; // 99
                $currentSet = 'C';

                continue;
            }
            if ($currentSet === 'C' && ! ctype_digit($flat[$i])) {
                $codes[] = self::CODE_B_FROM_C; // 100
                $currentSet = 'B';

                continue;
            }
            // Plain Set B encoding (default).
            if ($currentSet === 'B') {
                $byte = ord($flat[$i]);
                if ($byte < 32 || $byte > 126) {
                    throw new \InvalidArgumentException(sprintf(
                        'GS1-128 Set B got byte 0x%02X (outside 32..126)', $byte,
                    ));
                }
                $codes[] = $byte - 32;
                $i++;
            } else { // A
                $byte = ord($flat[$i]);
                $codes[] = $byte <= 31 ? $byte + 64 : $byte - 32;
                $i++;
            }
        }

        return $codes;
    }

    /**
     * Count consecutive digits starting at $i, stopping before any FNC1 boundary.
     *
     * @param  array<int, true>  $fnc1Positions
     */
    private static function countLeadingDigits(string $s, int $i, array $fnc1Positions): int
    {
        $count = 0;
        $n = strlen($s);
        while ($i + $count < $n && ctype_digit($s[$i + $count])) {
            if ($count > 0 && isset($fnc1Positions[$i + $count])) {
                break; // FNC1 inserted before this byte
            }
            $count++;
        }

        return $count;
    }

    /**
     * Parse "(NN)data(MM)data" → list of [ai, data] pairs.
     *
     * @return list<array{ai: string, data: string}>
     */
    private static function parseGs1Ais(string $input): array
    {
        $result = [];
        $offset = 0;
        $n = strlen($input);
        while ($offset < $n) {
            if ($input[$offset] !== '(') {
                throw new \InvalidArgumentException(sprintf(
                    'GS1-128 syntax error at position %d: expected "(", got %s', $offset, $input[$offset]
                ));
            }
            $close = strpos($input, ')', $offset);
            if ($close === false) {
                throw new \InvalidArgumentException('GS1-128 unmatched "(" in input');
            }
            $ai = substr($input, $offset + 1, $close - $offset - 1);
            if (! preg_match('@^\d{2,4}$@', $ai)) {
                throw new \InvalidArgumentException("GS1-128 invalid AI: ($ai)");
            }
            // Find next "(" or end.
            $next = strpos($input, '(', $close);
            $dataEnd = $next === false ? $n : $next;
            $data = substr($input, $close + 1, $dataEnd - $close - 1);
            if ($data === '') {
                throw new \InvalidArgumentException("GS1-128 AI ($ai) has no data");
            }
            $result[] = ['ai' => $ai, 'data' => $data];
            $offset = $dataEnd;
        }

        return $result;
    }

    /**
     * Heuristic auto-mode encoder. Returns list<int> data codewords
     * (including START codeword first). Caller adds checksum + STOP in finalize.
     *
     * Algorithm:
     *  1. Scan input charwise, classify: DIGIT / CTRL / PRINT (32..95 minus
     *     digits/lower) / LOWER.
     *  2. Greedy run detection: contiguous digit runs ≥4 (even length) → Set C.
     *  3. Ctrl run + no lowercase context → Set A.
     *  4. Default → Set B.
     *  5. Between runs emit explicit CODE_X switch (A/B/C codewords 99/100/101).
     *  6. Start codeword = best initial set based on first run.
     *
     * @return list<int>
     */
    private function encodeAuto(string $data): array
    {
        // Validate all bytes upfront — Code 128 supports ASCII 0..127 only.
        for ($i = 0; $i < strlen($data); $i++) {
            $b = ord($data[$i]);
            if ($b > 126) {
                throw new \InvalidArgumentException(sprintf(
                    'Code 128 supports ASCII 0..126 only; got byte 0x%02X',
                    $b,
                ));
            }
        }
        $runs = self::splitRunsForMode($data);
        $codes = [];
        $currentSet = null;
        foreach ($runs as $run) {
            $targetSet = $run['set'];
            $segment = $run['text'];
            if ($currentSet === null) {
                $codes[] = match ($targetSet) {
                    'A' => self::START_A,
                    'B' => self::START_B,
                    'C' => self::START_C,
                };
            } elseif ($currentSet !== $targetSet) {
                // Permanent switch.
                $codes[] = match ($currentSet.$targetSet) {
                    'AB', 'CB' => self::CODE_B_FROM_A, // 100
                    'BA', 'CA' => self::CODE_A_FROM_B, // 101
                    'AC', 'BC' => self::CODE_C_FROM_A, // 99
                };
            }
            // Encode segment in chosen set.
            if ($targetSet === 'C') {
                for ($i = 0; $i < strlen($segment); $i += 2) {
                    $codes[] = (int) substr($segment, $i, 2);
                }
            } elseif ($targetSet === 'A') {
                $bytes = array_values(unpack('C*', $segment) ?: []);
                foreach ($bytes as $byte) {
                    $codes[] = $byte <= 31 ? $byte + 64 : $byte - 32;
                }
            } else { // 'B'
                $bytes = array_values(unpack('C*', $segment) ?: []);
                foreach ($bytes as $byte) {
                    $codes[] = $byte - 32;
                }
            }
            $currentSet = $targetSet;
        }

        return $codes;
    }

    /**
     * Split input into mode runs. Greedy heuristic:
     *  - Digit run of length ≥4 (even length) — Set C
     *  - Run with control chars (ASCII 0..31) — Set A (if possible)
     *  - Otherwise — Set B
     *
     * For trailing odd digits in C-run, the last digit is deferred to the next B run.
     *
     * @return list<array{set: string, text: string}>
     */
    private static function splitRunsForMode(string $data): array
    {
        $n = strlen($data);
        // Fast path: if string contains control chars and NO lowercase —
        // entirely Set A (avoids B→A switch overhead).
        $hasControl = false;
        $hasLowercase = false;
        $hasDigitRunGE4 = false;
        for ($k = 0; $k < $n; $k++) {
            $bk = ord($data[$k]);
            if ($bk < 32) {
                $hasControl = true;
            }
            if ($bk >= 97 && $bk <= 122) {
                $hasLowercase = true;
            }
        }
        // Check for ≥4 consecutive digits.
        if (preg_match('@\d{4,}@', $data)) {
            $hasDigitRunGE4 = true;
        }
        if ($hasControl && ! $hasLowercase && ! $hasDigitRunGE4) {
            // Pure Set A path.
            return [['set' => 'A', 'text' => $data]];
        }
        $runs = [];
        $i = 0;
        while ($i < $n) {
            // Try Set C run: ≥4 consecutive digits, take even-length max.
            if (ctype_digit($data[$i])) {
                $j = $i;
                while ($j < $n && ctype_digit($data[$j])) {
                    $j++;
                }
                $digitLen = $j - $i;
                if ($digitLen >= 4) {
                    // Even length for Set C; if odd, leave last digit to next run.
                    $cLen = $digitLen - ($digitLen % 2);
                    $runs[] = ['set' => 'C', 'text' => substr($data, $i, $cLen)];
                    $i += $cLen;

                    continue;
                }
            }
            // Try Set A: control chars (0..31) — but only if no lowercase in current run.
            $byte = ord($data[$i]);
            if ($byte < 32) {
                $j = $i;
                while ($j < $n) {
                    $b = ord($data[$j]);
                    if ($b >= 97 && $b <= 122) {
                        break; // lowercase forces Set B
                    }
                    if ($b > 95) {
                        break; // out of Set A range
                    }
                    $j++;
                }
                if ($j > $i) {
                    $runs[] = ['set' => 'A', 'text' => substr($data, $i, $j - $i)];
                    $i = $j;

                    continue;
                }
            }
            // Set B: take chars until either digit-run start ≥4 OR control char
            // forcing switch to A/C.
            $j = $i;
            while ($j < $n) {
                $b = ord($data[$j]);
                if ($b < 32 || $b > 126) {
                    break;
                }
                // Look-ahead: digit run ≥4 starts here?
                if (ctype_digit($data[$j])) {
                    $k = $j;
                    while ($k < $n && ctype_digit($data[$k])) {
                        $k++;
                    }
                    if (($k - $j) >= 4) {
                        break; // delegate to next iteration → Set C
                    }
                }
                $j++;
            }
            if ($j > $i) {
                $runs[] = ['set' => 'B', 'text' => substr($data, $i, $j - $i)];
                $i = $j;

                continue;
            }
            // Fail-safe: advance by 1 to avoid infinite loop.
            $i++;
        }

        return $runs;
    }

    /**
     * Code 128 Set A — supports ASCII 0..95 (including control chars).
     *
     * Encoding:
     *  - ASCII 32..95 (' '..'_') → Set A values 0..63 (byte - 32).
     *  - ASCII 0..31 (control)  → Set A values 64..95 (byte + 64).
     */
    private function encodeSetA(string $data): void
    {
        if ($data === '') {
            throw new \InvalidArgumentException('Code 128 input must be non-empty');
        }
        $codes = [self::START_A];
        $bytes = array_values(unpack('C*', $data) ?: []);
        foreach ($bytes as $byte) {
            if ($byte >= 0 && $byte <= 31) {
                $codes[] = $byte + 64;
            } elseif ($byte >= 32 && $byte <= 95) {
                $codes[] = $byte - 32;
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Code 128 Set A supports ASCII 0..95 only; got byte 0x%02X',
                    $byte,
                ));
            }
        }
        $this->finalize($codes);
    }

    /**
     * @return list<bool>
     */
    public function modules(): array
    {
        return $this->modules;
    }

    public function moduleCount(): int
    {
        return count($this->modules);
    }

    /**
     * Quiet zone — 10 modules recommended by spec; minimum for readability.
     * Returns modules with pre/post-padded white space.
     *
     * @return list<bool>
     */
    public function modulesWithQuietZone(int $quietModules = 10): array
    {
        $quiet = array_fill(0, $quietModules, false);

        return [...$quiet, ...$this->modules, ...$quiet];
    }

    private function encodeSetB(string $data): void
    {
        if ($data === '') {
            throw new \InvalidArgumentException('Code 128 input must be non-empty');
        }

        $codes = [self::START_B];
        $bytes = array_values(unpack('C*', $data) ?: []);
        foreach ($bytes as $byte) {
            if ($byte < 32 || $byte > 126) {
                throw new \InvalidArgumentException(sprintf(
                    'Code 128 Set B supports ASCII 32..126 only; got byte 0x%02X',
                    $byte,
                ));
            }
            $codes[] = $byte - 32;
        }
        $this->finalize($codes);
    }

    /**
     * Code 128 Set C — encodes 2 digits per codeword. Requires
     * even-length digit-only input (caller validates).
     */
    private function encodeSetC(string $data): void
    {
        $codes = [self::START_C];
        for ($i = 0; $i < strlen($data); $i += 2) {
            $pair = (int) substr($data, $i, 2);
            $codes[] = $pair;
        }
        $this->finalize($codes);
    }

    /**
     * @param  list<int>  $codes  Start CW followed by data CWs.
     */
    private function finalize(array $codes): void
    {
        // Checksum: (start + Σ(value × position)) mod 103. Position 1-based
        // for data; start counts as position 0 (multiplier 1).
        $sum = $codes[0];
        $position = 1;
        for ($i = 1; $i < count($codes); $i++) {
            $sum += $codes[$i] * $position;
            $position++;
        }
        $checksum = $sum % 103;
        $codes[] = $checksum;
        $codes[] = self::STOP;

        // Render each code value into modules.
        foreach ($codes as $code) {
            $pattern = self::PATTERNS[$code];
            $bar = true; // Each pattern starts with black bar.
            foreach (str_split($pattern) as $widthStr) {
                $width = (int) $widthStr;
                for ($j = 0; $j < $width; $j++) {
                    $this->modules[] = $bar;
                }
                $bar = ! $bar;
            }
        }
    }
}
