<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * Code 39 barcode encoder.
 *
 * Code 39 (ISO/IEC 16388) — alphanumeric variable-length barcode. Encodes:
 *  - Digits 0-9
 *  - Uppercase A-Z
 *  - 7 symbols: `-`, `.`, ` `, `$`, `/`, `+`, `%`
 *
 * Each character = 9 elements (5 bars + 4 spaces), 3 of which are "wide"
 * (3× narrow width). Inter-character gap = 1 narrow space.
 * Start/stop = `*` (delimiter character).
 *
 * Optional Mod-43 check digit (self-check) via `withCheckDigit: true`.
 *
 * Self-checking property: no decoder-side error correction needed
 * (each character independently validated by pattern structure).
 */
final class Code39Encoder
{
    /**
     * Pattern table: char → 9-bit pattern where 1 = wide element,
     * 0 = narrow element. Position 0 = first bar; alternates bar/space.
     */
    private const PATTERNS = [
        '0' => '000110100',
        '1' => '100100001',
        '2' => '001100001',
        '3' => '101100000',
        '4' => '000110001',
        '5' => '100110000',
        '6' => '001110000',
        '7' => '000100101',
        '8' => '100100100',
        '9' => '001100100',
        'A' => '100001001',
        'B' => '001001001',
        'C' => '101001000',
        'D' => '000011001',
        'E' => '100011000',
        'F' => '001011000',
        'G' => '000001101',
        'H' => '100001100',
        'I' => '001001100',
        'J' => '000011100',
        'K' => '100000011',
        'L' => '001000011',
        'M' => '101000010',
        'N' => '000010011',
        'O' => '100010010',
        'P' => '001010010',
        'Q' => '000000111',
        'R' => '100000110',
        'S' => '001000110',
        'T' => '000010110',
        'U' => '110000001',
        'V' => '011000001',
        'W' => '111000000',
        'X' => '010010001',
        'Y' => '110010000',
        'Z' => '011010000',
        '-' => '010000101',
        '.' => '110000100',
        ' ' => '011000100',
        '$' => '010101000',
        '/' => '010100010',
        '+' => '010001010',
        '%' => '000101010',
        '*' => '010010100',
    ];

    /**
     * Mod-43 value table for check digit calculation.
     * Order: 0-9 (=0-9), A-Z (=10-35), -, ., (space), $, /, +, %.
     */
    private const MOD43_VALUES = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%';

    private const WIDE_RATIO = 3;

    /** @var list<bool> */
    private array $modules = [];

    /** Canonical encoded text (input + optional check digit). */
    public readonly string $canonical;

    /**
     * @param  string  $value  Input string (case-insensitive — lowercase
     *                         is automatically normalized to uppercase).
     * @param  bool  $withCheckDigit  Append Mod-43 check digit.
     */
    public function __construct(string $value, bool $withCheckDigit = false)
    {
        $value = strtoupper($value);
        if ($value === '') {
            throw new \InvalidArgumentException('Code 39 input must be non-empty');
        }
        for ($i = 0; $i < strlen($value); $i++) {
            $c = $value[$i];
            if ($c === '*') {
                throw new \InvalidArgumentException(
                    "Code 39 input may not contain '*' (reserved as start/stop)",
                );
            }
            if (! isset(self::PATTERNS[$c])) {
                throw new \InvalidArgumentException(
                    "Code 39 cannot encode character '$c' (0x".dechex(ord($c)).')',
                );
            }
        }

        if ($withCheckDigit) {
            $value .= self::checkDigitChar($value);
        }

        $this->canonical = $value;
        $this->encode($value);
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
     * Compute Mod-43 check digit character for input.
     * Sum of character values mod 43 → indexed back to MOD43_VALUES.
     */
    public static function checkDigitChar(string $value): string
    {
        $value = strtoupper($value);
        $sum = 0;
        for ($i = 0; $i < strlen($value); $i++) {
            $idx = strpos(self::MOD43_VALUES, $value[$i]);
            if ($idx === false) {
                throw new \InvalidArgumentException(
                    "Cannot compute Code 39 check for char '$value[$i]'",
                );
            }
            $sum += $idx;
        }

        return self::MOD43_VALUES[$sum % 43];
    }

    /**
     * Verify Mod-43 check digit. Expects $value to include trailing check char.
     */
    public static function verifyCheckDigit(string $value): bool
    {
        $value = strtoupper($value);
        if (strlen($value) < 2) {
            return false;
        }
        $data = substr($value, 0, -1);
        $check = substr($value, -1);

        return self::checkDigitChar($data) === $check;
    }

    private function encode(string $value): void
    {
        $bits = '';
        // Implicit start/stop = '*' wrapper.
        $chars = '*'.$value.'*';

        for ($i = 0; $i < strlen($chars); $i++) {
            $pattern = self::PATTERNS[$chars[$i]];
            for ($j = 0; $j < 9; $j++) {
                $isBar = ($j % 2 === 0);
                $width = $pattern[$j] === '1' ? self::WIDE_RATIO : 1;
                $bits .= str_repeat($isBar ? '1' : '0', $width);
            }
            // Inter-character gap (1 narrow space) — except after last char.
            if ($i < strlen($chars) - 1) {
                $bits .= '0';
            }
        }

        $this->modules = array_map(fn (string $c): bool => $c === '1', str_split($bits));
    }
}
