<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * Code 93 barcode encoder.
 *
 * Code 93 (USS Code 93) — alphanumeric variable-length barcode, denser
 * successor to Code 39 with mandatory dual Mod-47 check digits (C, K).
 *
 * Encoding:
 *  - 47 character set: 0-9, A-Z (uppercase only), `-`, `.`, ` `, `$`, `/`,
 *    `+`, `%`
 *  - Implicit `*` start/stop wrapper + 1-module termination bar
 *  - Continuous encoding (NO inter-character gaps — each char exactly
 *    9 modules: 3 bars + 3 spaces, alternating)
 *  - Two automatic check digits C (weight 1..20 cycling) and K (weight
 *    1..15 cycling), both Mod-47
 *
 * Module count formula: (N + 4) × 9 + 1 = 9N + 37
 * (N data chars + start + stop + 2 check digits, plus termination bar)
 */
final class Code93Encoder
{
    /**
     * Pattern table: char → 9-bit module sequence. Continuous encoding —
     * each char exactly 9 modules. Position 0 = first bar.
     */
    private const PATTERNS = [
        '0' => '100010100',
        '1' => '101001000',
        '2' => '101000100',
        '3' => '101000010',
        '4' => '100101000',
        '5' => '100100100',
        '6' => '100100010',
        '7' => '101010000',
        '8' => '100010010',
        '9' => '100001010',
        'A' => '110101000',
        'B' => '110100100',
        'C' => '110100010',
        'D' => '110010100',
        'E' => '110010010',
        'F' => '110001010',
        'G' => '101101000',
        'H' => '101100100',
        'I' => '101100010',
        'J' => '100110100',
        'K' => '100011010',
        'L' => '101011000',
        'M' => '101001100',
        'N' => '101000110',
        'O' => '100101100',
        'P' => '100010110',
        'Q' => '110110100',
        'R' => '110110010',
        'S' => '110101100',
        'T' => '110100110',
        'U' => '110010110',
        'V' => '110011010',
        'W' => '101101100',
        'X' => '101100110',
        'Y' => '100110110',
        'Z' => '100111010',
        '-' => '100101110',
        '.' => '111010100',
        ' ' => '111010010',
        '$' => '111001010',
        '/' => '101101110',
        '+' => '101110110',
        '%' => '110101110',
        // Shift characters for Full ASCII Code 93 — placeholders
        // chr(0xC1..0xC4). Not accepted in user input, but check digits may
        // hash into their value positions (43-46).
        "\xC1" => '100100110', // ($) shift
        "\xC2" => '111011010', // (%) shift
        "\xC3" => '111010110', // (/) shift
        "\xC4" => '100110010', // (+) shift
        '*' => '101011110',
    ];

    /**
     * Value table for Mod-47 check digit computation.
     * 47 chars at positions 0..46 — 43 standard + 4 shift placeholders.
     */
    private const VALUES = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. \$/+%\xC1\xC2\xC3\xC4";

    /**
     * User-allowed character set (43 chars — without shift placeholders).
     * Used for validation; shifts are only added via check digits.
     */
    private const STANDARD_CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%';

    /** Mod base. */
    private const MOD = 47;

    /** @var list<bool> */
    private array $modules = [];

    /** User-facing data (without auto-appended check digits and delimiters). */
    public readonly string $canonical;

    /** First check digit character (computed). */
    public readonly string $checkC;

    /** Second check digit character (computed). */
    public readonly string $checkK;

    public function __construct(string $value)
    {
        $value = strtoupper($value);
        if ($value === '') {
            throw new \InvalidArgumentException('Code 93 input must be non-empty');
        }
        for ($i = 0; $i < strlen($value); $i++) {
            $c = $value[$i];
            if ($c === '*') {
                throw new \InvalidArgumentException(
                    "Code 93 input may not contain '*' (reserved as start/stop)",
                );
            }
            if (strpos(self::STANDARD_CHARS, $c) === false) {
                throw new \InvalidArgumentException(
                    "Code 93 cannot encode character '$c' (0x".dechex(ord($c)).')',
                );
            }
        }

        $cValue = self::computeCheckDigit($value, 20);
        $cChar = self::VALUES[$cValue];
        $kValue = self::computeCheckDigit($value.$cChar, 15);
        $kChar = self::VALUES[$kValue];

        $this->canonical = $value;
        $this->checkC = $cChar;
        $this->checkK = $kChar;
        $this->encode('*'.$value.$cChar.$kChar.'*');
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
     * @return list<bool>
     */
    public function modulesWithQuietZone(int $quietModules = 10): array
    {
        $quiet = array_fill(0, $quietModules, false);

        return [...$quiet, ...$this->modules, ...$quiet];
    }

    /**
     * Mod-47 weighted check digit computation.
     * Weights cycle 1..maxWeight from right to left.
     */
    public static function computeCheckDigit(string $value, int $maxWeight): int
    {
        $value = strtoupper($value);
        $sum = 0;
        $len = strlen($value);
        for ($i = 0; $i < $len; $i++) {
            $weight = ($i % $maxWeight) + 1;
            $idx = strpos(self::VALUES, $value[$len - 1 - $i]);
            if ($idx === false) {
                throw new \InvalidArgumentException(
                    "Code 93 cannot compute check digit for '{$value[$len - 1 - $i]}'",
                );
            }
            $sum += $idx * $weight;
        }

        return $sum % self::MOD;
    }

    private function encode(string $fullValue): void
    {
        $bits = '';
        for ($i = 0; $i < strlen($fullValue); $i++) {
            $bits .= self::PATTERNS[$fullValue[$i]];
        }
        // Termination bar — single narrow bar after stop char.
        $bits .= '1';

        $this->modules = array_map(fn (string $c): bool => $c === '1', str_split($bits));
    }
}
