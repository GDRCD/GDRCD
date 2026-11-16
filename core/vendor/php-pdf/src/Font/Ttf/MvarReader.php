<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * `MVAR` table — Metric Variations.
 *
 * Defines per-axis adjustments to font-wide metrics (ascender, descender,
 * cap height, x-height, line gap, sub/super-script positions, strikeout,
 * underline, etc.).
 *
 * Format v1.0:
 *   uint16 majorVersion (=1)
 *   uint16 minorVersion (=0)
 *   uint16 reserved
 *   uint16 valueRecordSize (=8)
 *   uint16 valueRecordCount
 *   Offset16 itemVariationStoreOffset
 *   ValueRecord[valueRecordCount]
 *
 * ValueRecord (8 bytes):
 *   Tag tag (4 bytes)  — e.g., 'asc ', 'desc', 'lgap', 'cpht', 'xhgt'
 *   uint16 deltaSetOuterIndex
 *   uint16 deltaSetInnerIndex
 *
 * Common tags (per OpenType spec):
 *   'asc ' hhea ascender
 *   'desc' hhea descender
 *   'lgap' hhea lineGap
 *   'hcla' OS/2 sTypoAscender
 *   'hcld' OS/2 sTypoDescender
 *   'hclg' OS/2 sTypoLineGap
 *   'cpht' OS/2 sCapHeight
 *   'xhgt' OS/2 sxHeight
 *   'undo' post underlineOffset
 *   'unds' post underlineThickness
 *   'strs' OS/2 yStrikeoutSize
 *   'stro' OS/2 yStrikeoutPosition
 */
final class MvarReader
{
    public function __construct(
        public readonly ItemVariationStore $ivs,
        /** @var array<string, array{outer:int, inner:int}> tag → indices */
        public readonly array $records,
    ) {
    }

    /**
     * @param  array{offset:int, length:int}  $tableInfo
     */
    public static function read(string $bytes, array $tableInfo): self
    {
        $offset = $tableInfo['offset'];
        $recordSize = self::u16($bytes, $offset + 6);
        $recordCount = self::u16($bytes, $offset + 8);
        $ivsOff = self::u16($bytes, $offset + 10);

        $records = [];
        $cursor = $offset + 12;
        for ($i = 0; $i < $recordCount; $i++) {
            $tag = substr($bytes, $cursor, 4);
            $outer = self::u16($bytes, $cursor + 4);
            $inner = self::u16($bytes, $cursor + 6);
            $records[$tag] = ['outer' => $outer, 'inner' => $inner];
            $cursor += $recordSize;
        }

        $ivs = ItemVariationStore::parse($bytes, $offset + $ivsOff);

        return new self($ivs, $records);
    }

    /**
     * Compute delta for metric identified by 4-char tag.
     *
     * @param  array<int, float>  $normCoords
     */
    public function metricDelta(string $tag, array $normCoords): float
    {
        $rec = $this->records[$tag] ?? null;
        if ($rec === null) {
            return 0.0;
        }

        return $this->ivs->delta($rec['outer'], $rec['inner'], $normCoords);
    }

    private static function u16(string $b, int $o): int
    {
        return (ord($b[$o]) << 8) | ord($b[$o + 1]);
    }
}
