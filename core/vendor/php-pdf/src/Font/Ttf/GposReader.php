<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * GPOS table parser — extracts pair-wise kerning data into a KerningTable.
 *
 * Scope:
 *  - lookup type 2 (Pair Adjustment Positioning) format 1 + format 2
 *  - valueFormat: only the X_ADVANCE bit (0x0004). Other positional
 *    adjustments (xPlacement, yAdvance, yPlacement, device tables) are
 *    not critical for Western European typography and are ignored.
 *
 * Not covered:
 *  - GPOS lookup type 1 (single positioning) — usually for diacritic
 *    placement, not for kerning
 *  - lookup type 3 (cursive attachment) — for script fonts
 *  - lookup type 4-8 (mark positioning, contextual, etc.) — Arabic/Indic
 *  - Script/feature filtering — we grab ALL pair-adjustment lookups
 *    as kerning (most fonts have a single lookup type 2 = kern)
 *
 * Reference: OpenType GPOS spec, https://docs.microsoft.com/typography/opentype/spec/gpos
 */
final class GposReader
{
    private const VALUE_X_PLACEMENT = 0x0001;

    private const VALUE_Y_PLACEMENT = 0x0002;

    private const VALUE_X_ADVANCE = 0x0004;

    private const VALUE_Y_ADVANCE = 0x0008;

    private const VALUE_X_PLA_DEVICE = 0x0010;

    private const VALUE_Y_PLA_DEVICE = 0x0020;

    private const VALUE_X_ADV_DEVICE = 0x0040;

    private const VALUE_Y_ADV_DEVICE = 0x0080;

    /**
     * @param  array{offset: int, length: int}  $gposTableInfo
     */
    public function read(string $sourceBytes, array $gposTableInfo): KerningTable
    {
        $table = new KerningTable;
        $base = $gposTableInfo['offset'];
        $reader = new BinaryReader($sourceBytes);

        // GPOS Header:
        //   uint16 majorVersion
        //   uint16 minorVersion
        //   uint16 scriptListOffset       (from base)
        //   uint16 featureListOffset
        //   uint16 lookupListOffset
        $reader->seek($base);
        $reader->skip(2); // majorVersion
        $reader->skip(2); // minorVersion
        $reader->skip(2); // scriptListOffset — we do not filter by script
        $reader->skip(2); // featureListOffset — we do not filter by feature
        $lookupListOffset = $reader->readUInt16();

        $this->readLookupList($sourceBytes, $base + $lookupListOffset, $table);

        return $table;
    }

    /**
     * LookupList:
     *   uint16 lookupCount
     *   Offset16 lookups[lookupCount]  (from start of LookupList)
     */
    private function readLookupList(string $bytes, int $listOffset, KerningTable $table): void
    {
        $reader = new BinaryReader($bytes);
        $reader->seek($listOffset);
        $lookupCount = $reader->readUInt16();

        for ($i = 0; $i < $lookupCount; $i++) {
            $reader->seek($listOffset + 2 + $i * 2);
            $lookupOffset = $reader->readUInt16();
            $this->readLookup($bytes, $listOffset + $lookupOffset, $table);
        }
    }

    /**
     * Lookup table:
     *   uint16 lookupType (we care about 2 = Pair Adjustment)
     *   uint16 lookupFlag
     *   uint16 subTableCount
     *   Offset16 subtableOffsets[subTableCount]   (from Lookup start)
     *   (optional uint16 markFilteringSet if flag & 0x10)
     */
    private function readLookup(string $bytes, int $lookupOffset, KerningTable $table): void
    {
        $reader = new BinaryReader($bytes);
        $reader->seek($lookupOffset);
        $lookupType = $reader->readUInt16();
        $reader->skip(2); // lookupFlag
        $subTableCount = $reader->readUInt16();

        if ($lookupType !== 2) {
            return; // not pair adjustment — ignore
        }

        for ($i = 0; $i < $subTableCount; $i++) {
            $reader->seek($lookupOffset + 6 + $i * 2);
            $subtableOffset = $reader->readUInt16();
            $this->readPairAdjustmentSubtable($bytes, $lookupOffset + $subtableOffset, $table);
        }
    }

    /**
     * Pair Adjustment subtable — dispatcher on format 1 or 2.
     *
     * Format 1 (specific pairs):
     *   uint16 posFormat (= 1)
     *   Offset16 coverage  (glyphs that participate as 'first')
     *   uint16 valueFormat1  (bits)
     *   uint16 valueFormat2
     *   uint16 pairSetCount
     *   Offset16 pairSetOffsets[pairSetCount]
     *
     * Format 2 (class-based):
     *   uint16 posFormat (= 2)
     *   Offset16 coverage
     *   uint16 valueFormat1
     *   uint16 valueFormat2
     *   Offset16 classDef1
     *   Offset16 classDef2
     *   uint16 class1Count
     *   uint16 class2Count
     *   Class1Record class1Records[class1Count]:
     *     Class2Record class2Records[class2Count]:
     *       ValueRecord value1
     *       ValueRecord value2
     */
    private function readPairAdjustmentSubtable(string $bytes, int $subOffset, KerningTable $table): void
    {
        $reader = new BinaryReader($bytes);
        $reader->seek($subOffset);
        $posFormat = $reader->readUInt16();
        $coverageOffset = $reader->readUInt16();
        $valueFormat1 = $reader->readUInt16();
        $valueFormat2 = $reader->readUInt16();

        if (($valueFormat1 & self::VALUE_X_ADVANCE) === 0) {
            return; // No X_ADVANCE for the first glyph — not kerning in our sense
        }

        // Parse coverage list — glyph IDs that can be 'first'.
        $coverageGlyphs = $this->readCoverage($bytes, $subOffset + $coverageOffset);
        if ($coverageGlyphs === []) {
            return;
        }

        if ($posFormat === 1) {
            $this->readFormat1($bytes, $reader, $subOffset, $coverageGlyphs, $valueFormat1, $valueFormat2, $table);
        } elseif ($posFormat === 2) {
            $this->readFormat2($bytes, $reader, $subOffset, $coverageGlyphs, $valueFormat1, $valueFormat2, $table);
        }
    }

    /**
     * @param  list<int>  $coverageGlyphs
     */
    private function readFormat1(
        string $bytes,
        BinaryReader $reader,
        int $subOffset,
        array $coverageGlyphs,
        int $valueFormat1,
        int $valueFormat2,
        KerningTable $table,
    ): void {
        // Reader position: after valueFormat2. Next:
        //   uint16 pairSetCount
        //   Offset16 pairSetOffsets[pairSetCount]
        $pairSetCount = $reader->readUInt16();
        if ($pairSetCount !== count($coverageGlyphs)) {
            return; // malformed
        }

        $valueSize1 = $this->valueRecordSize($valueFormat1);
        $valueSize2 = $this->valueRecordSize($valueFormat2);

        for ($i = 0; $i < $pairSetCount; $i++) {
            $firstGid = $coverageGlyphs[$i];
            $reader->seek($subOffset + 10 + $i * 2);
            $pairSetOffset = $reader->readUInt16();
            $this->readPairSet(
                $bytes,
                $subOffset + $pairSetOffset,
                $firstGid,
                $valueFormat1,
                $valueFormat2,
                $valueSize1,
                $valueSize2,
                $table,
            );
        }
    }

    private function readPairSet(
        string $bytes,
        int $pairSetOffset,
        int $firstGid,
        int $valueFormat1,
        int $valueFormat2,
        int $valueSize1,
        int $valueSize2,
        KerningTable $table,
    ): void {
        $reader = new BinaryReader($bytes);
        $reader->seek($pairSetOffset);
        $pairValueCount = $reader->readUInt16();

        for ($j = 0; $j < $pairValueCount; $j++) {
            $secondGid = $reader->readUInt16();
            $value1 = $this->readValueRecord($reader, $valueFormat1);
            // Skip value2 (we only use value1.xAdvance).
            $reader->skip($valueSize2);

            $xAdvance = $value1['xAdvance'];
            if ($xAdvance !== 0) {
                $table->add($firstGid, $secondGid, $xAdvance);
            }
        }
    }

    /**
     * @param  list<int>  $coverageGlyphs
     */
    private function readFormat2(
        string $bytes,
        BinaryReader $reader,
        int $subOffset,
        array $coverageGlyphs,
        int $valueFormat1,
        int $valueFormat2,
        KerningTable $table,
    ): void {
        // Reader position: after valueFormat2.
        //   Offset16 classDef1
        //   Offset16 classDef2
        //   uint16 class1Count
        //   uint16 class2Count
        $classDef1Offset = $reader->readUInt16();
        $classDef2Offset = $reader->readUInt16();
        $class1Count = $reader->readUInt16();
        $class2Count = $reader->readUInt16();

        $classDef1 = $this->readClassDef($bytes, $subOffset + $classDef1Offset);
        $classDef2 = $this->readClassDef($bytes, $subOffset + $classDef2Offset);

        $valueSize1 = $this->valueRecordSize($valueFormat1);
        $valueSize2 = $this->valueRecordSize($valueFormat2);
        $recordSize = $valueSize1 + $valueSize2;

        // Class records — array class1Count × class2Count.
        // Each record contains value1 + value2 for the (class1, class2) pair.
        $classRecordsBase = $reader->tell();

        // Pre-compute (class1, class2) → xAdvance — skip cells that have
        // zero adjustments (most pairs).
        $kerningByClass = []; // class1 → class2 → xAdvance
        for ($c1 = 0; $c1 < $class1Count; $c1++) {
            for ($c2 = 0; $c2 < $class2Count; $c2++) {
                $cellOffset = $classRecordsBase + ($c1 * $class2Count + $c2) * $recordSize;
                $reader->seek($cellOffset);
                $value1 = $this->readValueRecord($reader, $valueFormat1);
                $xAdvance = $value1['xAdvance'];
                if ($xAdvance !== 0) {
                    $kerningByClass[$c1][$c2] = $xAdvance;
                }
            }
        }

        // Expand (class1, class2) → (gid1, gid2). For each covered
        // first-glyph (i.e. in the coverage list) we look up class1, then
        // for each class2 with a non-zero adjustment we add a pair for
        // all gid2 in class2.
        //
        // Class 0 = "default class", contains all glyphs that are not
        // assigned an explicit class. For kerning we care only about
        // explicit-classed glyphs in Coverage (for first) and in
        // classDef2 (for second).

        // Reverse classDef2: class → list<gid>.
        $class2Members = [];
        foreach ($classDef2 as $gid => $class) {
            $class2Members[$class][] = $gid;
        }

        foreach ($coverageGlyphs as $firstGid) {
            $c1 = $classDef1[$firstGid] ?? 0;
            if (! isset($kerningByClass[$c1])) {
                continue;
            }
            foreach ($kerningByClass[$c1] as $c2 => $xAdvance) {
                foreach ($class2Members[$c2] ?? [] as $secondGid) {
                    $table->add($firstGid, $secondGid, $xAdvance);
                }
            }
        }
    }

    /**
     * Coverage table (referenced by GPOS subtables) — two formats:
     *
     * Format 1: explicit list
     *   uint16 coverageFormat (= 1)
     *   uint16 glyphCount
     *   uint16 glyphArray[glyphCount]
     *
     * Format 2: ranges
     *   uint16 coverageFormat (= 2)
     *   uint16 rangeCount
     *   RangeRecord rangeRecords[rangeCount]:
     *     uint16 startGlyphID
     *     uint16 endGlyphID
     *     uint16 startCoverageIndex  (we do not use this; needed for GSUB)
     *
     * @return list<int>  ordered list of glyph IDs in coverage
     */
    private function readCoverage(string $bytes, int $offset): array
    {
        $reader = new BinaryReader($bytes);
        $reader->seek($offset);
        $format = $reader->readUInt16();
        $out = [];
        if ($format === 1) {
            $count = $reader->readUInt16();
            for ($i = 0; $i < $count; $i++) {
                $out[] = $reader->readUInt16();
            }
        } elseif ($format === 2) {
            $rangeCount = $reader->readUInt16();
            for ($i = 0; $i < $rangeCount; $i++) {
                $start = $reader->readUInt16();
                $end = $reader->readUInt16();
                $reader->skip(2); // startCoverageIndex
                for ($g = $start; $g <= $end; $g++) {
                    $out[] = $g;
                }
            }
        }

        return $out;
    }

    /**
     * ClassDef table — two formats:
     *
     * Format 1: consecutive glyph range
     *   uint16 classFormat (= 1)
     *   uint16 startGlyphID
     *   uint16 glyphCount
     *   uint16 classValueArray[glyphCount]
     *
     * Format 2: ranges with class
     *   uint16 classFormat (= 2)
     *   uint16 classRangeCount
     *   ClassRangeRecord rangeRecords[classRangeCount]:
     *     uint16 startGlyphID
     *     uint16 endGlyphID
     *     uint16 class
     *
     * Glyphs not in ClassDef → class 0 (default).
     *
     * @return array<int, int>  glyphId → class (only non-zero classes)
     */
    private function readClassDef(string $bytes, int $offset): array
    {
        $reader = new BinaryReader($bytes);
        $reader->seek($offset);
        $format = $reader->readUInt16();
        $out = [];
        if ($format === 1) {
            $startGid = $reader->readUInt16();
            $count = $reader->readUInt16();
            for ($i = 0; $i < $count; $i++) {
                $class = $reader->readUInt16();
                if ($class !== 0) {
                    $out[$startGid + $i] = $class;
                }
            }
        } elseif ($format === 2) {
            $rangeCount = $reader->readUInt16();
            for ($i = 0; $i < $rangeCount; $i++) {
                $start = $reader->readUInt16();
                $end = $reader->readUInt16();
                $class = $reader->readUInt16();
                if ($class === 0) {
                    continue;
                }
                for ($g = $start; $g <= $end; $g++) {
                    $out[$g] = $class;
                }
            }
        }

        return $out;
    }

    /**
     * ValueRecord size in bytes — depends on valueFormat bits.
     * Each set bit = 2 bytes (int16 value or uint16 device table offset).
     */
    private function valueRecordSize(int $valueFormat): int
    {
        $size = 0;
        for ($bit = 0; $bit < 8; $bit++) {
            if ($valueFormat & (1 << $bit)) {
                $size += 2;
            }
        }

        return $size;
    }

    /**
     * Reads ValueRecord by valueFormat. Returns only xAdvance — the only
     * thing we care about for kerning.
     *
     * @return array{xAdvance: int}
     */
    private function readValueRecord(BinaryReader $reader, int $valueFormat): array
    {
        $xAdvance = 0;
        if ($valueFormat & self::VALUE_X_PLACEMENT) {
            $reader->skip(2);
        }
        if ($valueFormat & self::VALUE_Y_PLACEMENT) {
            $reader->skip(2);
        }
        if ($valueFormat & self::VALUE_X_ADVANCE) {
            $xAdvance = $reader->readInt16();
        }
        if ($valueFormat & self::VALUE_Y_ADVANCE) {
            $reader->skip(2);
        }
        // Device tables — uint16 offsets (skip).
        if ($valueFormat & self::VALUE_X_PLA_DEVICE) {
            $reader->skip(2);
        }
        if ($valueFormat & self::VALUE_Y_PLA_DEVICE) {
            $reader->skip(2);
        }
        if ($valueFormat & self::VALUE_X_ADV_DEVICE) {
            $reader->skip(2);
        }
        if ($valueFormat & self::VALUE_Y_ADV_DEVICE) {
            $reader->skip(2);
        }

        return ['xAdvance' => $xAdvance];
    }
}
