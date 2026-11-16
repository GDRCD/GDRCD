<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * Apply gvar deltas + IUP to glyph outlines for a chosen instance.
 *
 * Given user-space axis coordinates (e.g., ['wght' => 700]), produces
 * modified glyf table bytes with interpolated outlines.
 *
 * Algorithm per simple glyph:
 *  1. Parse simple glyph contours.
 *  2. Get gvar deltas for normalized coords.
 *  3. Apply IUP (Interpolation of Unreferenced Points): for each contour,
 *     points without an explicit delta interpolate proportionally from
 *     neighbors with deltas.
 *  4. Re-serialize glyph bytes.
 *
 * Composite glyph behavior:
 *  - Component glyph outlines transformed individually.
 *  - Per-component (dx, dy) anchor offsets transformed via gvar deltas
 *    on the composite glyph's point space. Point N = component N's
 *    anchor. Plus 4 phantom points for advance metrics (handled separately).
 *  - Transform matrices (scale, x/y scale, 2x2) preserved as-is — variation
 *    affects only anchor positions, not scale factors.
 */
final class VariableInstance
{
    /**
     * @param  array<string, float>  $userCoords  axis tag → user value
     */
    public function __construct(
        private readonly TtfFile $ttf,
        public readonly array $userCoords,
    ) {}

    public function isVariable(): bool
    {
        return $this->ttf->isVariable() && $this->ttf->gvar() !== null;
    }

    /**
     * Apply variation deltas to raw glyph bytes (one glyph). Returns
     * modified bytes — or original $glyphBytes if glyph is composite/
     * empty/non-variable.
     */
    public function transformGlyph(int $glyphId, string $glyphBytes): string
    {
        if (! $this->isVariable() || $glyphBytes === '') {
            return $glyphBytes;
        }
        // Composite glyph — apply gvar deltas to component anchor offsets.
        $composite = CompositeGlyph::parse($glyphBytes);
        if ($composite !== null) {
            return $this->transformComposite($glyphId, $composite);
        }
        $glyph = SimpleGlyph::parse($glyphBytes);
        if ($glyph === null) {
            return $glyphBytes; // unknown or empty — skip
        }

        $gvar = $this->ttf->gvar();
        $norm = $this->ttf->normalizeCoordinates($this->userCoords);
        $pointCount = count($glyph->xCoords);
        $deltas = $gvar->glyphDeltas($glyphId, $norm, $pointCount);

        if ($deltas === []) {
            return $glyphBytes;
        }

        // Apply explicit deltas + IUP for unreferenced points.
        $newX = $glyph->xCoords;
        $newY = $glyph->yCoords;
        $hasDelta = [];
        foreach ($deltas as $idx => $d) {
            if ($idx < $pointCount) {
                $newX[$idx] = (int) round($newX[$idx] + $d['x']);
                $newY[$idx] = (int) round($newY[$idx] + $d['y']);
                $hasDelta[$idx] = true;
            }
        }

        // IUP per contour.
        $contourStart = 0;
        foreach ($glyph->endPts as $end) {
            self::iupContour($newX, $glyph->xCoords, $hasDelta, $contourStart, $end);
            self::iupContour($newY, $glyph->yCoords, $hasDelta, $contourStart, $end);
            $contourStart = $end + 1;
        }

        return $glyph->serialize($newX, $newY);
    }

    /**
     * Apply gvar deltas to composite glyph component anchor offsets.
     *
     * For composite glyphs, gvar point indices map to component anchors:
     * Point 0 = component 0's anchor (dx, dy)
     * Point 1 = component 1's anchor
     * ...
     * Plus 4 phantom points for advance metrics (skipped here).
     */
    private function transformComposite(int $glyphId, CompositeGlyph $composite): string
    {
        $gvar = $this->ttf->gvar();
        $norm = $this->ttf->normalizeCoordinates($this->userCoords);
        $componentCount = count($composite->components);
        // Composite glyph has componentCount + 4 phantom points in gvar.
        $deltas = $gvar->glyphDeltas($glyphId, $norm, $componentCount + 4);
        if ($deltas === []) {
            return $composite->originalBytes;
        }
        $newOffsets = [];
        foreach ($composite->components as $idx => $comp) {
            if (! $comp['isXY']) {
                continue; // anchor-point alignment, not (dx, dy) — skip
            }
            $delta = $deltas[$idx] ?? null;
            if ($delta === null) {
                continue;
            }
            $newOffsets[$idx] = [
                'dx' => (int) round($comp['arg1'] + $delta['x']),
                'dy' => (int) round($comp['arg2'] + $delta['y']),
            ];
        }
        if ($newOffsets === []) {
            return $composite->originalBytes;
        }

        return $composite->serialize($newOffsets);
    }

    /**
     * IUP (Interpolation of Unreferenced Points) per OpenType spec §11.7.
     *
     * For each contiguous range of unreferenced points between referenced
     * points P_a and P_b on the contour:
     *   - If P_a and P_b have same original coord: shift unreferenced
     *     points by same delta as P_a (= P_b).
     *   - If the original coord of an unreferenced point lies between
     *     P_a and P_b: linearly interpolate based on position ratio.
     *   - Else: shift by closer referenced point's delta.
     *
     * @param  list<int>  $newCoords  modified coords (modified in-place)
     * @param  list<int>  $origCoords original coords (read-only)
     * @param  array<int, bool>  $hasDelta  points that have explicit delta
     */
    private static function iupContour(array &$newCoords, array $origCoords, array $hasDelta, int $start, int $end): void
    {
        $length = $end - $start + 1;
        if ($length === 0) {
            return;
        }
        // Find all referenced points in contour.
        $refs = [];
        for ($i = $start; $i <= $end; $i++) {
            if (isset($hasDelta[$i])) {
                $refs[] = $i;
            }
        }
        if ($refs === []) {
            return; // No referenced points — leave contour untouched.
        }

        // For each unreferenced point, find next ref forward and prev ref backward
        // (wrapping around contour boundary).
        $numRefs = count($refs);
        for ($i = 0; $i < $numRefs; $i++) {
            $r1 = $refs[$i];
            $r2 = $refs[($i + 1) % $numRefs];
            // Range from r1+1 .. r2-1 (wrapping inside contour).
            self::iupRange($newCoords, $origCoords, $r1, $r2, $start, $end);
        }
    }

    /**
     * Interpolate points in range (r1, r2) exclusive endpoints. Range wraps
     * within contour [$start, $end].
     *
     * @param  list<int>  $newCoords
     * @param  list<int>  $origCoords
     */
    private static function iupRange(array &$newCoords, array $origCoords, int $r1, int $r2, int $start, int $end): void
    {
        if ($r1 === $r2) {
            return;
        }
        $length = $end - $start + 1;
        $oa = $origCoords[$r1];
        $ob = $origCoords[$r2];
        $deltaA = $newCoords[$r1] - $oa;
        $deltaB = $newCoords[$r2] - $ob;

        // Iterate through points between r1 and r2 (exclusive), wrapping around contour.
        $i = $r1 + 1;
        while (true) {
            if ($i > $end) {
                $i = $start;
            }
            if ($i === $r2) {
                break;
            }
            $oi = $origCoords[$i];
            $oMin = min($oa, $ob);
            $oMax = max($oa, $ob);
            if ($oi >= $oMin && $oi <= $oMax) {
                // Linear interpolation.
                if ($oa === $ob) {
                    // Both refs equal — shift by deltaA (== deltaB likely).
                    $newCoords[$i] = (int) round($oi + ($deltaA + $deltaB) / 2);
                } else {
                    $t = ($oi - $oa) / ($ob - $oa);
                    $delta = $deltaA + $t * ($deltaB - $deltaA);
                    $newCoords[$i] = (int) round($oi + $delta);
                }
            } else {
                // Use closer endpoint's delta.
                $useDelta = (abs($oi - $oa) < abs($oi - $ob)) ? $deltaA : $deltaB;
                $newCoords[$i] = (int) round($oi + $useDelta);
            }
            $i++;
        }
    }
}
