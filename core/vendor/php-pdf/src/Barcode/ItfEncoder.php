<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * Interleaved 2 of 5 (ITF) barcode encoder.
 *
 * ITF (ISO/IEC 16390) — numeric variable-length barcode. Common profile:
 * ITF-14 (14-digit GTIN-14) for shipping container labeling.
 *
 * Encoding:
 *  - Digits 0-9 only, even number of digits (interleaved pairs)
 *  - Each digit = 5 elements (3 narrow + 2 wide → hence "2 of 5")
 *  - Each pair: first digit's pattern → 5 bars, second's → 5 spaces (interleaved)
 *  - Wide:narrow ratio = 2:1 (standard)
 *  - Start = narrow bar + narrow space + narrow bar + narrow space (4 mod)
 *  - Stop = wide bar + narrow space + narrow bar (4 mod for 2:1)
 *
 * Module count: 4 (start) + 7 × N (digits) + 4 (stop) = 8 + 7N
 * Optional Mod-10 check digit (GTIN-style) via `withCheckDigit: true`.
 */
final class ItfEncoder
{
    /**
     * Pattern: digit → 5-bit string where 1 = wide element, 0 = narrow.
     * Each pattern has exactly 2 wide elements (the "2 of 5" property).
     */
    private const PATTERNS = [
        '0' => '00110',
        '1' => '10001',
        '2' => '01001',
        '3' => '11000',
        '4' => '00101',
        '5' => '10100',
        '6' => '01100',
        '7' => '00011',
        '8' => '10010',
        '9' => '01010',
    ];

    private const WIDE_RATIO = 2;

    /** @var list<bool> */
    private array $modules = [];

    /** Canonical encoded digits (input + optional check). */
    public readonly string $canonical;

    /**
     * @param  string  $digits  Numeric input, even length after optional check digit.
     * @param  bool  $withCheckDigit  Append Mod-10 GTIN-style check digit.
     */
    public function __construct(string $digits, bool $withCheckDigit = false)
    {
        if (! preg_match('@^\d+$@', $digits)) {
            throw new \InvalidArgumentException('ITF input must be digits only');
        }
        if ($digits === '') {
            throw new \InvalidArgumentException('ITF input must be non-empty');
        }

        if ($withCheckDigit) {
            $digits .= (string) self::computeCheckDigit($digits);
        }

        if (strlen($digits) % 2 !== 0) {
            throw new \InvalidArgumentException(
                'ITF requires even number of digits (pairs are interleaved), got '.strlen($digits),
            );
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
     * Quiet zone: ITF requires substantial quiet zone (10× narrow module
     * width per spec). Default 10 modules.
     *
     * @return list<bool>
     */
    public function modulesWithQuietZone(int $quietModules = 10): array
    {
        $quiet = array_fill(0, $quietModules, false);

        return [...$quiet, ...$this->modules, ...$quiet];
    }

    /**
     * GTIN-style Mod-10 weighted check digit. Right-to-left weights 3,1,3,1...
     * (i.e. rightmost data digit weight 3).
     *
     * Works for any data length: GTIN-8, GTIN-12, GTIN-13, GTIN-14.
     */
    public static function computeCheckDigit(string $digits): int
    {
        if ($digits === '' || ! preg_match('@^\d+$@', $digits)) {
            throw new \InvalidArgumentException('computeCheckDigit expects non-empty digit string');
        }
        $sum = 0;
        $len = strlen($digits);
        for ($i = 0; $i < $len; $i++) {
            $d = (int) $digits[$len - 1 - $i];
            $sum += ($i % 2 === 0) ? $d * 3 : $d;
        }

        return (10 - $sum % 10) % 10;
    }

    private function encode(string $digits): void
    {
        $bits = '';

        // Start pattern: narrow bar, narrow space, narrow bar, narrow space.
        $bits .= '1010';

        // Interleave digit pairs.
        $len = strlen($digits);
        for ($p = 0; $p < $len; $p += 2) {
            $a = self::PATTERNS[$digits[$p]];
            $b = self::PATTERNS[$digits[$p + 1]];
            for ($i = 0; $i < 5; $i++) {
                $barWidth = $a[$i] === '1' ? self::WIDE_RATIO : 1;
                $spaceWidth = $b[$i] === '1' ? self::WIDE_RATIO : 1;
                $bits .= str_repeat('1', $barWidth);
                $bits .= str_repeat('0', $spaceWidth);
            }
        }

        // Stop pattern: wide bar, narrow space, narrow bar.
        $bits .= str_repeat('1', self::WIDE_RATIO).'0'.'1';

        $this->modules = array_map(fn (string $c): bool => $c === '1', str_split($bits));
    }
}
