<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * MSI Plessey (Modified Plessey) barcode encoder.
 *
 * Variable-length numeric barcode common in retail shelf labeling and
 * inventory tracking. Each digit = 4 bits BCD encoded MSB first.
 *
 * Bit encoding (3 modules per bit):
 *  - `0` bit → wide bar (none) + narrow space → "100" (bar=1, space=2)
 *  - `1` bit → wide bar (2) + narrow space (1) → "110"
 *  *Note*: bar widths are 1 (narrow), 2 (wide); each bit's bar+space totals 3.
 *
 * Start pattern: "110" (single 1-bit pattern, 3 modules)
 * Stop pattern: "1001" (4 modules)
 *
 * Module count = 3 + 12·N + 4 = 12·N + 7
 *
 * Optional Mod-10 check digit (most common) via `withCheckDigit: true`.
 * Mod-11 is not implemented (rarely used in production).
 */
final class MsiPlesseyEncoder
{
    /** Each digit → 12-bit pattern (4 bits × 3 modules each). */
    private const PATTERNS = [
        '0' => '100100100100', // 0000
        '1' => '100100100110', // 0001
        '2' => '100100110100', // 0010
        '3' => '100100110110', // 0011
        '4' => '100110100100', // 0100
        '5' => '100110100110', // 0101
        '6' => '100110110100', // 0110
        '7' => '100110110110', // 0111
        '8' => '110100100100', // 1000
        '9' => '110100100110', // 1001
    ];

    private const START_PATTERN = '110';

    private const STOP_PATTERN = '1001';

    /** @var list<bool> */
    private array $modules = [];

    /** Encoded canonical (input + optional check digit). */
    public readonly string $canonical;

    /**
     * @param  string  $digits  Numeric input, non-empty.
     * @param  bool  $withCheckDigit  Append Mod-10 check digit.
     */
    public function __construct(string $digits, bool $withCheckDigit = false)
    {
        if (! preg_match('@^\d+$@', $digits)) {
            throw new \InvalidArgumentException('MSI Plessey input must be digits only');
        }
        if ($digits === '') {
            throw new \InvalidArgumentException('MSI Plessey input must be non-empty');
        }

        if ($withCheckDigit) {
            $digits .= (string) self::computeCheckDigit($digits);
        }

        $this->canonical = $digits;
        $this->encode($digits);
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
    public function modulesWithQuietZone(int $quietModules = 12): array
    {
        $quiet = array_fill(0, $quietModules, false);

        return [...$quiet, ...$this->modules, ...$quiet];
    }

    /**
     * Mod-10 check digit (Luhn-style for MSI):
     *  1. Take odd-positioned digits from right (positions 1,3,5...)
     *  2. Concatenate as integer, multiply by 2, sum decimal digits
     *  3. Add even-positioned digits from right
     *  4. Check = (10 - sum % 10) % 10
     */
    public static function computeCheckDigit(string $digits): int
    {
        if ($digits === '' || ! preg_match('@^\d+$@', $digits)) {
            throw new \InvalidArgumentException('computeCheckDigit expects non-empty digit string');
        }

        // Collect odd-positioned (from right, 1-indexed) and even-positioned digits.
        $oddConcat = '';
        $evenSum = 0;
        $len = strlen($digits);
        for ($i = 0; $i < $len; $i++) {
            $pos = $i + 1; // 1-indexed from right
            $d = (int) $digits[$len - 1 - $i];
            if ($pos % 2 === 1) {
                $oddConcat .= (string) $d;
            } else {
                $evenSum += $d;
            }
        }

        // Reverse to get original left-to-right order, multiply by 2.
        $doubled = (int) strrev($oddConcat) * 2;
        $doubledSum = 0;
        foreach (str_split((string) $doubled) as $ch) {
            $doubledSum += (int) $ch;
        }

        $total = $doubledSum + $evenSum;

        return (10 - $total % 10) % 10;
    }

    private function encode(string $digits): void
    {
        $bits = self::START_PATTERN;
        for ($i = 0; $i < strlen($digits); $i++) {
            $bits .= self::PATTERNS[$digits[$i]];
        }
        $bits .= self::STOP_PATTERN;

        $this->modules = array_map(fn (string $c): bool => $c === '1', str_split($bits));
    }
}
