<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * Pharmacode (Laetus / Pharmaceutical Binary Code) encoder.
 *
 * One-track pharmaceutical barcode for pill packaging, blister packs.
 * Simplest of all 1D barcode formats — no start/stop, no check digit,
 * no character set (numeric only). Range: 3..131070.
 *
 * Encoding algorithm:
 *   while N > 0:
 *     if N is even: emit "wide" bar; N = N/2 - 1
 *     else:         emit "narrow" bar; N = (N-1)/2
 *
 * Modules per element (per Laetus spec):
 *  - narrow bar: 1 module (1X)
 *  - wide bar:   3 modules (3X)
 *  - inter-bar space: 2 modules (2X)
 *
 * Width formula = (1·N_narrow + 3·N_wide) + 2·(bars - 1)
 * Min 4 modules (N=3 → narrow + space + narrow). Max ~80 modules (N=131070
 * = 16 wide bars).
 */
final class PharmacodeEncoder
{
    public const MIN_VALUE = 3;

    public const MAX_VALUE = 131070;

    private const NARROW_WIDTH = 1;

    private const WIDE_WIDTH = 3;

    private const SPACE_WIDTH = 2;

    /** @var list<bool> */
    private array $modules = [];

    /** Encoded value (3..131070). */
    public readonly int $value;

    /**
     * Bar sequence — list<bool> where true = wide, false = narrow.
     * Left-to-right rendering order.
     *
     * @var list<bool>
     */
    public readonly array $bars;

    /**
     * @param  int  $value  3..131070 inclusive.
     */
    public function __construct(int $value)
    {
        if ($value < self::MIN_VALUE || $value > self::MAX_VALUE) {
            throw new \InvalidArgumentException(sprintf(
                'Pharmacode value must be in [%d, %d], got %d',
                self::MIN_VALUE,
                self::MAX_VALUE,
                $value,
            ));
        }

        $this->value = $value;
        $this->bars = self::buildBars($value);
        $this->encode();
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
    public function modulesWithQuietZone(int $quietModules = 6): array
    {
        $quiet = array_fill(0, $quietModules, false);

        return [...$quiet, ...$this->modules, ...$quiet];
    }

    /**
     * Build the bar sequence for value. Returns list<bool> where
     * true = wide, false = narrow. Bars are in left-to-right rendering order.
     *
     * @return list<bool>
     */
    public static function buildBars(int $value): array
    {
        if ($value < self::MIN_VALUE || $value > self::MAX_VALUE) {
            throw new \InvalidArgumentException('Pharmacode value out of range');
        }
        $bars = [];
        while ($value > 0) {
            if ($value % 2 === 0) {
                $bars[] = true; // wide
                $value = intdiv($value, 2) - 1;
            } else {
                $bars[] = false; // narrow
                $value = intdiv($value - 1, 2);
            }
        }

        return $bars;
    }

    private function encode(): void
    {
        $bits = '';
        $n = count($this->bars);
        for ($i = 0; $i < $n; $i++) {
            $width = $this->bars[$i] ? self::WIDE_WIDTH : self::NARROW_WIDTH;
            $bits .= str_repeat('1', $width);
            if ($i < $n - 1) {
                $bits .= str_repeat('0', self::SPACE_WIDTH);
            }
        }

        $this->modules = array_map(fn (string $c): bool => $c === '1', str_split($bits));
    }
}
