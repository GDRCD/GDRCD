<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * Code 11 (USS Code 11) barcode encoder.
 *
 * Numeric + dash variable-length barcode, primarily used in telecom for
 * labeling telephone equipment, also lab samples.
 *
 * Encoding:
 *  - 11 characters: 0-9 + `-` (dash)
 *  - Each char = 5 elements (3 bars + 2 spaces) with 1 or 2 wide elements
 *  - Wide:narrow ratio = 2:1
 *  - Module count per char: 6 or 7 depending on pattern
 *  - Implicit `*` start/stop wrapper + 1-narrow inter-char gap
 *
 * Optional Mod-11 check digits:
 *  - C (single): for inputs ≤ 10 chars — appended automatically when
 *    `withCheckDigit: true`
 *  - C + K (double): for inputs > 10 chars — when `doubleCheck: true`
 */
final class Code11Encoder
{
    /**
     * Pattern: char → 5-bit width pattern (1 = wide, 0 = narrow).
     * Position 0 = first bar; alternates bar/space.
     */
    private const PATTERNS = [
        '0' => '00001',
        '1' => '10001',
        '2' => '01001',
        '3' => '11000',
        '4' => '00101',
        '5' => '10100',
        '6' => '01100',
        '7' => '00011',
        '8' => '10010',
        '9' => '10000',
        '-' => '00010',
        '*' => '00110', // start/stop
    ];

    /** Value table for Mod-11 check digit. */
    private const VALUES = '0123456789-';

    private const WIDE_RATIO = 2;

    /** @var list<bool> */
    private array $modules = [];

    /** Canonical encoded value (input + optional check digits). */
    public readonly string $canonical;

    /**
     * @param  string  $value  Numeric input + optional `-` characters.
     * @param  bool  $withCheckDigit  Append Mod-11 C check digit.
     * @param  bool  $doubleCheck  Append C + K check digits (for inputs > 10 chars).
     */
    public function __construct(
        string $value,
        bool $withCheckDigit = false,
        bool $doubleCheck = false,
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException('Code 11 input must be non-empty');
        }
        for ($i = 0; $i < strlen($value); $i++) {
            $c = $value[$i];
            if ($c === '*') {
                throw new \InvalidArgumentException(
                    "Code 11 input may not contain '*' (reserved as start/stop)",
                );
            }
            if (strpos(self::VALUES, $c) === false) {
                throw new \InvalidArgumentException(
                    "Code 11 cannot encode character '$c'",
                );
            }
        }

        if ($doubleCheck) {
            $c = self::computeCheckDigit($value, 10);
            $value .= self::VALUES[$c];
            $k = self::computeCheckDigit($value, 9);
            $value .= self::VALUES[$k];
        } elseif ($withCheckDigit) {
            $c = self::computeCheckDigit($value, 10);
            $value .= self::VALUES[$c];
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
     * Mod-11 check digit. Weights cycle 1..maxWeight from right.
     *
     * @param  int  $maxWeight  10 for C check, 9 for K check.
     */
    public static function computeCheckDigit(string $value, int $maxWeight = 10): int
    {
        $sum = 0;
        $len = strlen($value);
        for ($i = 0; $i < $len; $i++) {
            $c = $value[$len - 1 - $i];
            $idx = strpos(self::VALUES, $c);
            if ($idx === false) {
                throw new \InvalidArgumentException("Cannot compute Code 11 check for '$c'");
            }
            $weight = ($i % $maxWeight) + 1;
            $sum += $idx * $weight;
        }

        return $sum % 11;
    }

    private function encode(string $value): void
    {
        $bits = '';
        $chars = '*'.$value.'*';
        $len = strlen($chars);

        for ($i = 0; $i < $len; $i++) {
            $pattern = self::PATTERNS[$chars[$i]];
            for ($j = 0; $j < 5; $j++) {
                $isBar = ($j % 2 === 0);
                $width = $pattern[$j] === '1' ? self::WIDE_RATIO : 1;
                $bits .= str_repeat($isBar ? '1' : '0', $width);
            }
            if ($i < $len - 1) {
                $bits .= '0'; // 1-narrow inter-char gap
            }
        }

        $this->modules = array_map(fn (string $c): bool => $c === '1', str_split($bits));
    }
}
