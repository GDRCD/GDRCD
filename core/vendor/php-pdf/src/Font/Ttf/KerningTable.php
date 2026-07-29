<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * Kerning lookup table — pair-wise advance adjustments.
 *
 * Query:
 *   $kern->lookup(leftGid, rightGid): int  // xAdvance adjustment in FUnits
 *                                            // (negative = pair closer)
 *
 * Source:
 *  - GPOS lookup type 2 "Pair Adjustment Positioning", format 1 (specific
 *    pairs) and format 2 (class-based pairs). Parsed by GposReader.
 *  - Legacy `kern` table (Apple TT) — not supported here; modern fonts
 *    use GPOS exclusively.
 *
 * Use cases:
 *  - TextMeasurer: accounts for kerning when measuring pt-width of a string
 *  - PdfFont: emits TJ-operator with position adjustments in content stream
 *
 * Performance: O(1) lookup via nested-array indexing.
 */
final class KerningTable
{
    /** @var array<int, array<int, int>>  leftGid → rightGid → xAdvance FU */
    private array $pairs = [];

    private int $pairCount = 0;

    public function add(int $leftGid, int $rightGid, int $xAdvanceFu): void
    {
        if ($xAdvanceFu === 0) {
            return; // do not store zero-adjustment pairs
        }
        if (! isset($this->pairs[$leftGid])) {
            $this->pairs[$leftGid] = [];
        }
        if (! isset($this->pairs[$leftGid][$rightGid])) {
            $this->pairCount++;
        }
        $this->pairs[$leftGid][$rightGid] = $xAdvanceFu;
    }

    /**
     * Lookup xAdvance adjustment for pair (left, right). Returns 0 if
     * the pair is not defined (no kerning).
     */
    public function lookup(int $leftGid, int $rightGid): int
    {
        return $this->pairs[$leftGid][$rightGid] ?? 0;
    }

    public function isEmpty(): bool
    {
        return $this->pairCount === 0;
    }

    public function pairCount(): int
    {
        return $this->pairCount;
    }
}
