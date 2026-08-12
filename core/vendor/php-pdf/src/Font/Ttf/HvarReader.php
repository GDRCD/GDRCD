<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * `HVAR` table — Horizontal Metric Variations.
 *
 * Provides per-glyph advance-width deltas for variable fonts. The optional
 * LSB/RSB tables are not needed for simple metric interpolation (PDF does
 * not use side bearings directly).
 *
 * Format v1.0:
 *   uint16 majorVersion (=1)
 *   uint16 minorVersion
 *   Offset32 itemVariationStoreOffset
 *   Offset32 advanceWidthMappingOffset (0 = direct glyphID lookup)
 *   Offset32 LSBMappingOffset (often 0)
 *   Offset32 RSBMappingOffset (often 0)
 *
 * DeltaSetIndexMap (used by all 3 mappings):
 *   uint16 entryFormat
 *     bits 0..3: innerIndexBitSize - 1
 *     bits 4..5: mapEntrySize - 1  (1, 2, 3, or 4 bytes)
 *   uint16 mapCount
 *   uint8[] mapData
 *
 * Each map entry: mapEntrySize bytes. Top bits = outerIndex, bottom
 * (innerIndexBitSize) bits = innerIndex.
 */
final class HvarReader
{
    public function __construct(
        public readonly ItemVariationStore $ivs,
        /** @var array<int, array{outer:int, inner:int}>|null  null = direct glyphID → (0, glyphID) */
        public readonly ?array $advanceMap,
    ) {
    }

    /**
     * @param  array{offset:int, length:int}  $tableInfo
     */
    public static function read(string $bytes, array $tableInfo): self
    {
        $offset = $tableInfo['offset'];
        $ivsOff = self::u32($bytes, $offset + 4);
        $advMapOff = self::u32($bytes, $offset + 8);

        $ivs = ItemVariationStore::parse($bytes, $offset + $ivsOff);
        $advMap = $advMapOff === 0
            ? null
            : self::readDeltaSetIndexMap($bytes, $offset + $advMapOff);

        return new self($ivs, $advMap);
    }

    /**
     * Compute the interpolated advance width delta for a glyph under
     * normalized coords.
     *
     * @param  array<int, float>  $normCoords
     */
    public function advanceDelta(int $glyphId, array $normCoords): float
    {
        if ($this->advanceMap === null) {
            $outer = 0;
            $inner = $glyphId;
        } else {
            $entry = $this->advanceMap[$glyphId] ?? $this->advanceMap[count($this->advanceMap) - 1] ?? null;
            if ($entry === null) {
                return 0.0;
            }
            $outer = $entry['outer'];
            $inner = $entry['inner'];
        }

        return $this->ivs->delta($outer, $inner, $normCoords);
    }

    /**
     * @return array<int, array{outer:int, inner:int}>
     */
    private static function readDeltaSetIndexMap(string $bytes, int $offset): array
    {
        $entryFormat = self::u16($bytes, $offset);
        $mapCount = self::u16($bytes, $offset + 2);
        $innerBits = ($entryFormat & 0x0F) + 1;
        $entrySize = (($entryFormat >> 4) & 0x03) + 1;
        $innerMask = (1 << $innerBits) - 1;

        $map = [];
        $cursor = $offset + 4;
        for ($i = 0; $i < $mapCount; $i++) {
            $val = 0;
            for ($k = 0; $k < $entrySize; $k++) {
                $val = ($val << 8) | ord($bytes[$cursor + $k]);
            }
            $cursor += $entrySize;
            $map[$i] = [
                'outer' => $val >> $innerBits,
                'inner' => $val & $innerMask,
            ];
        }

        return $map;
    }

    private static function u16(string $b, int $o): int
    {
        return (ord($b[$o]) << 8) | ord($b[$o + 1]);
    }

    private static function u32(string $b, int $o): int
    {
        return (ord($b[$o]) << 24) | (ord($b[$o + 1]) << 16) | (ord($b[$o + 2]) << 8) | ord($b[$o + 3]);
    }
}
