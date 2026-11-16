<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * OpenType Item Variation Store (shared between HVAR, MVAR, GDEF, COLR).
 *
 * Stores a set of variation regions + delta sets. Each delta set describes
 * how an item value changes when normalized axis coords change.
 *
 * Per OpenType spec §6.2.7:
 *
 * ItemVariationStore:
 *   uint16  format (=1)
 *   Offset32 variationRegionListOffset
 *   uint16  itemVariationDataCount
 *   Offset32 itemVariationDataOffsets[]
 *
 * Region list:
 *   uint16  axisCount
 *   uint16  regionCount
 *   VariationRegion[regionCount]
 *
 * VariationRegion:
 *   RegionAxisCoordinates[axisCount]
 *
 * RegionAxisCoordinates:
 *   F2DOT14 start, peak, end (signed 2.14 fixed)
 *
 * ItemVariationData:
 *   uint16 itemCount
 *   uint16 shortDeltaCount
 *   uint16 regionIndexCount
 *   uint16 regionIndices[regionIndexCount]
 *   DeltaSet[itemCount]
 *
 * DeltaSet: shortDeltaCount × int16, then (regionIndexCount-shortDeltaCount) × int8.
 *
 * Long-format variation data (newer fonts): wordDeltaCount uses int32 for first
 * deltaCount items, with optional WORD_DELTAS flag — for now treated like int16.
 */
final class ItemVariationStore
{
    /**
     * @param  list<list<array{start:float, peak:float, end:float}>>  $regions
     * @param  list<array{regionIndices:list<int>, deltas:list<list<int>>}>  $subtables
     */
    public function __construct(
        public readonly array $regions,
        public readonly array $subtables,
    ) {}

    /**
     * Parse ItemVariationStore starting at $offset in $bytes.
     */
    public static function parse(string $bytes, int $offset): self
    {
        $format = self::u16($bytes, $offset);
        if ($format !== 1) {
            throw new \UnexpectedValueException("Unsupported ItemVariationStore format: $format");
        }
        $regionListOffset = self::u32($bytes, $offset + 2);
        $itemVarDataCount = self::u16($bytes, $offset + 6);
        $itemVarOffsets = [];
        for ($i = 0; $i < $itemVarDataCount; $i++) {
            $itemVarOffsets[] = self::u32($bytes, $offset + 8 + 4 * $i);
        }

        // Region list at $offset + $regionListOffset.
        $rlStart = $offset + $regionListOffset;
        $axisCount = self::u16($bytes, $rlStart);
        $regionCount = self::u16($bytes, $rlStart + 2);
        $regions = [];
        $regionEntrySize = 6 * $axisCount;
        for ($r = 0; $r < $regionCount; $r++) {
            $regionStart = $rlStart + 4 + $r * $regionEntrySize;
            $axesData = [];
            for ($a = 0; $a < $axisCount; $a++) {
                $axesData[] = [
                    'start' => self::f2dot14($bytes, $regionStart + 6 * $a),
                    'peak' => self::f2dot14($bytes, $regionStart + 6 * $a + 2),
                    'end' => self::f2dot14($bytes, $regionStart + 6 * $a + 4),
                ];
            }
            $regions[] = $axesData;
        }

        // ItemVariationData subtables.
        $subtables = [];
        foreach ($itemVarOffsets as $sub) {
            $s = $offset + $sub;
            $itemCount = self::u16($bytes, $s);
            $shortDeltaCount = self::u16($bytes, $s + 2);
            $regionIdxCount = self::u16($bytes, $s + 4);
            $regionIndices = [];
            for ($i = 0; $i < $regionIdxCount; $i++) {
                $regionIndices[] = self::u16($bytes, $s + 6 + 2 * $i);
            }
            // Delta sets follow.
            $deltaSetStart = $s + 6 + 2 * $regionIdxCount;
            $deltaSetSize = $shortDeltaCount * 2 + ($regionIdxCount - $shortDeltaCount);
            $deltas = [];
            for ($i = 0; $i < $itemCount; $i++) {
                $row = [];
                $base = $deltaSetStart + $i * $deltaSetSize;
                for ($k = 0; $k < $shortDeltaCount; $k++) {
                    $row[] = self::s16($bytes, $base + 2 * $k);
                }
                for ($k = 0; $k < $regionIdxCount - $shortDeltaCount; $k++) {
                    $row[] = self::s8($bytes, $base + 2 * $shortDeltaCount + $k);
                }
                $deltas[] = $row;
            }
            $subtables[] = ['regionIndices' => $regionIndices, 'deltas' => $deltas];
        }

        return new self($regions, $subtables);
    }

    /**
     * Compute the interpolated delta for (outerIndex, innerIndex) under
     * the given normalized axis coordinates.
     *
     * @param  array<int, float>  $normCoords  axis index → normalized value (-1..+1)
     */
    public function delta(int $outerIndex, int $innerIndex, array $normCoords): float
    {
        $sub = $this->subtables[$outerIndex] ?? null;
        if ($sub === null || ! isset($sub['deltas'][$innerIndex])) {
            return 0.0;
        }
        $deltaRow = $sub['deltas'][$innerIndex];
        $sum = 0.0;
        foreach ($sub['regionIndices'] as $i => $regionIdx) {
            $region = $this->regions[$regionIdx] ?? null;
            if ($region === null) {
                continue;
            }
            $scalar = self::regionScalar($region, $normCoords);
            if ($scalar === 0.0) {
                continue;
            }
            $sum += $deltaRow[$i] * $scalar;
        }

        return $sum;
    }

    /**
     * Region scalar = product across axes of per-axis tent factors.
     * Per OpenType spec §6.2.7.1.
     *
     * @param  list<array{start:float, peak:float, end:float}>  $region
     * @param  array<int, float>  $normCoords
     */
    private static function regionScalar(array $region, array $normCoords): float
    {
        $scalar = 1.0;
        foreach ($region as $axisIdx => $tent) {
            $coord = $normCoords[$axisIdx] ?? 0.0;
            $start = $tent['start'];
            $peak = $tent['peak'];
            $end = $tent['end'];
            // Per spec: skip if peak is 0 AND start <= 0 <= end (axis not involved).
            // Simplified: if peak == 0 then scalar contribution = 1 (no effect).
            if ($peak === 0.0) {
                continue;
            }
            if ($coord < $start || $coord > $end) {
                return 0.0;
            }
            if ($coord === $peak) {
                continue; // axis factor = 1
            }
            if ($coord < $peak) {
                $scalar *= ($coord - $start) / ($peak - $start);
            } else {
                $scalar *= ($end - $coord) / ($end - $peak);
            }
        }

        return $scalar;
    }

    // ── Binary readers ───────────────────────────────────────────────────

    private static function u16(string $b, int $o): int
    {
        return (ord($b[$o]) << 8) | ord($b[$o + 1]);
    }

    private static function s16(string $b, int $o): int
    {
        $v = self::u16($b, $o);

        return $v >= 0x8000 ? $v - 0x10000 : $v;
    }

    private static function u32(string $b, int $o): int
    {
        return (ord($b[$o]) << 24) | (ord($b[$o + 1]) << 16) | (ord($b[$o + 2]) << 8) | ord($b[$o + 3]);
    }

    private static function s8(string $b, int $o): int
    {
        $v = ord($b[$o]);

        return $v >= 0x80 ? $v - 0x100 : $v;
    }

    /** Signed 2.14 fixed: top 2 bits + sign = integer part [-2..1], low 14 = fraction. */
    private static function f2dot14(string $b, int $o): float
    {
        $raw = self::s16($b, $o);

        return $raw / 16384.0;
    }
}
