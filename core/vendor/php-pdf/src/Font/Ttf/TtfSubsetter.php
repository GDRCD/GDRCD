<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * Minimal TTF subsetter — cuts unused glyph outlines from the `glyf`
 * table and zeroes the corresponding `loca` entries. This is the main
 * size win for PDF embedding (from ~410KB Liberation Sans Regular down
 * to ~5-30KB depending on the number of unique glyphs).
 *
 * Algorithm (simplified):
 *
 *   1. Parse the `loca` table (offsets into `glyf`).
 *   2. For each glyphId:
 *      - if glyphId == 0 (.notdef) OR in $usedGlyphs → keep outline
 *      - else → outline becomes 0 bytes (loca[i] == loca[i+1])
 *   3. For glyphs that remain, check composite-references (component
 *      pointers): include referenced glyphs too.
 *   4. Repack `glyf` with cleaned-up entries + new `loca` offsets.
 *   5. Emit subset TTF (all other tables copied as-is).
 *
 * Limitations of this implementation:
 *  - Does NOT recompute table checksums or the whole-file checksum in
 *    `head`. PDF readers are tolerant — most do not check. Adobe Acrobat
 *    and Preview certainly do not, per empirical experience with mpdf.
 *  - Does NOT subset hmtx (numHMetrics stays the same; widths for unused
 *    glyphs remain in hmtx). This gives a small overhead (~5-15 KB
 *    typically) but avoids renumbering complexity.
 *  - Does NOT process GPOS/GSUB tables (they could be subset when
 *    kerning/ligatures are enabled). Here they are copied as-is.
 *  - Composite glyph dependencies — followed through components via
 *    flag bit 0x20 (MORE_COMPONENTS); transitive refs are tracked.
 */
final class TtfSubsetter
{
    /**
     * LRU cache for cross-Writer subset dedup.
     *
     * Same TtfFile instance + same glyph set + same variation axes →
     * bit-identical subset bytes. Caching saves subsetting + serialization
     * compute time in batch scenarios (e.g., generating 100 invoices from
     * the same template font).
     *
     * @var array<string, string>  key → subset bytes
     */
    private static array $cache = [];

    /** LRU cache size limit (oldest entries evicted on overflow). */
    private const CACHE_MAX = 32;

    /** Force-clear cache (mostly for testing). */
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /** Current cache size (for testing / monitoring). */
    public static function cacheSize(): int
    {
        return count(self::$cache);
    }

    /**
     * Subset a full TTF into a minimal version with only the specified
     * glyph IDs.
     *
     * If $variableInstance is provided, glyph outlines are pre-interpolated
     * via gvar deltas (the FontFile2 stream becomes "frozen" to chosen
     * axis values — variation tables are not embedded in the subset).
     *
     * LRU caching by (source identity, sorted glyphs, axes).
     *
     * @param  array<int, bool>|list<int>  $usedGlyphIds  If a list — converted
     *                                            to a flipped map for O(1) lookup.
     * @return string  Subset TTF bytes (for embedding as a FontFile2 stream).
     */
    public function subset(TtfFile $source, array $usedGlyphIds, ?VariableInstance $variableInstance = null): string
    {
        // Normalize input into a lookup map.
        $used = $this->normalizeUsedGlyphs($usedGlyphIds);
        // Glyph 0 (.notdef) — ALWAYS.
        $used[0] = true;

        // Cache key — source instance ID + sorted glyphs + axes.
        $glyphList = array_keys($used);
        sort($glyphList);
        $axesKey = $variableInstance !== null
            ? serialize($variableInstance->userCoords)
            : '';
        $cacheKey = spl_object_id($source).'|'.implode(',', $glyphList).'|'.$axesKey;

        if (isset(self::$cache[$cacheKey])) {
            // LRU touch — move to end (most recently used).
            $value = self::$cache[$cacheKey];
            unset(self::$cache[$cacheKey]);
            self::$cache[$cacheKey] = $value;

            return $value;
        }

        // Read global TTF parameters.
        $sourceBytes = $source->rawBytes();
        $reader = new BinaryReader($sourceBytes);

        // Parse table directory from source.
        $tables = $this->readTableDirectory($reader);

        $headInfo = $this->readHead($reader, $tables);
        $indexToLocFormat = $headInfo['indexToLocFormat']; // 0 = uint16, 1 = uint32

        $maxpInfo = $this->readMaxp($reader, $tables);
        $numGlyphs = $maxpInfo['numGlyphs'];

        // Parse loca offsets.
        $locaOffsets = $this->readLocaOffsets($reader, $tables, $numGlyphs, $indexToLocFormat);

        // Include composite references transitively.
        $this->addCompositeComponents($sourceBytes, $tables['glyf']['offset'], $locaOffsets, $used);

        // Build new glyf + loca.
        [$newGlyf, $newLocaOffsets] = $this->buildSubsetGlyf(
            $sourceBytes,
            $tables['glyf']['offset'],
            $locaOffsets,
            $used,
            $variableInstance,
        );
        $newLoca = $this->packLoca($newLocaOffsets, $indexToLocFormat);

        // Emit subset TTF. All tables (except glyf/loca) are copied as-is.
        $result = $this->buildOutputTtf($sourceBytes, $tables, $newGlyf, $newLoca);

        // Store in LRU cache; evict oldest if over limit.
        if (count(self::$cache) >= self::CACHE_MAX) {
            // PHP arrays preserve insertion order — array_shift evicts oldest.
            array_shift(self::$cache);
        }
        self::$cache[$cacheKey] = $result;

        return $result;
    }

    /**
     * @param  array<int, bool>|list<int>  $input
     * @return array<int, bool>
     */
    private function normalizeUsedGlyphs(array $input): array
    {
        if ($input === []) {
            return [];
        }
        $first = array_key_first($input);
        if (is_int($first) && is_bool($input[$first])) {
            return $input;
        }
        // List form → flip values to keys.
        $out = [];
        foreach ($input as $gid) {
            $out[(int) $gid] = true;
        }

        return $out;
    }

    /**
     * @return array<string, array{offset: int, length: int, tag: string}>
     */
    private function readTableDirectory(BinaryReader $reader): array
    {
        $reader->seek(0);
        $reader->skip(4); // scaler
        $numTables = $reader->readUInt16();
        $reader->skip(6); // searchRange + entrySelector + rangeShift

        $tables = [];
        for ($i = 0; $i < $numTables; $i++) {
            $tag = $reader->readTag();
            $reader->skip(4); // checksum
            $offset = $reader->readUInt32();
            $length = $reader->readUInt32();
            $tables[$tag] = ['offset' => $offset, 'length' => $length, 'tag' => $tag];
        }

        return $tables;
    }

    /**
     * @param  array<string, array{offset: int, length: int, tag: string}>  $tables
     * @return array{indexToLocFormat: int}
     */
    private function readHead(BinaryReader $reader, array $tables): array
    {
        $headOffset = $tables['head']['offset'];
        // indexToLocFormat is at offset 50 (head version 1.0).
        return ['indexToLocFormat' => $reader->peekUInt16($headOffset + 50)];
    }

    /**
     * @param  array<string, array{offset: int, length: int, tag: string}>  $tables
     * @return array{numGlyphs: int}
     */
    private function readMaxp(BinaryReader $reader, array $tables): array
    {
        // numGlyphs at offset 4 in maxp table.
        return ['numGlyphs' => $reader->peekUInt16($tables['maxp']['offset'] + 4)];
    }

    /**
     * loca contains numGlyphs+1 offsets (last sentinel marks end of glyf).
     * Format 0: offsets — uint16, multiplied by 2 for actual byte offset.
     * Format 1: uint32 (direct byte offset).
     *
     * @param  array<string, array{offset: int, length: int, tag: string}>  $tables
     * @return list<int>
     */
    private function readLocaOffsets(BinaryReader $reader, array $tables, int $numGlyphs, int $format): array
    {
        $locaOffset = $tables['loca']['offset'];
        $offsets = [];
        $reader->seek($locaOffset);
        for ($i = 0; $i <= $numGlyphs; $i++) {
            $offsets[] = $format === 0
                ? $reader->readUInt16() * 2
                : $reader->readUInt32();
        }

        return $offsets;
    }

    /**
     * Composite glyph — contains references to other glyphs via the
     * `compositeGlyphHeader.glyphIndex` field. Flag bit 0x20
     * (MORE_COMPONENTS) means another component follows.
     *
     * Glyph header (single + composite):
     *   int16 numberOfContours (negative = composite)
     *   int16 × 4 bbox
     *   If composite: repeating entries:
     *     uint16 flags
     *     uint16 glyphIndex
     *     ...args (var size depending on flags)
     *     while flags & 0x20
     *
     * We lazily walk composite refs and add all referenced glyphs to
     * $used. Recursively (composite of composite).
     *
     * @param  list<int>  $locaOffsets
     * @param  array<int, bool>  $used   modified in place
     */
    private function addCompositeComponents(
        string $sourceBytes,
        int $glyfBaseOffset,
        array $locaOffsets,
        array &$used,
    ): void {
        $stack = array_keys($used);
        $seen = $used;
        while ($stack !== []) {
            $gid = array_pop($stack);
            $start = $glyfBaseOffset + $locaOffsets[$gid];
            $end = $glyfBaseOffset + $locaOffsets[$gid + 1];
            if ($end - $start < 10) {
                continue; // empty/simple glyph; no composite refs
            }
            // numberOfContours at offset 0.
            $nc = unpack('n', substr($sourceBytes, $start, 2))[1];
            $nc = $nc >= 0x8000 ? $nc - 0x10000 : $nc; // sign extend
            if ($nc >= 0) {
                continue; // simple glyph — no components
            }
            // Composite — iterate components.
            $pos = $start + 10; // skip header + bbox
            while (true) {
                $flags = unpack('n', substr($sourceBytes, $pos, 2))[1];
                $componentGid = unpack('n', substr($sourceBytes, $pos + 2, 2))[1];
                if (! isset($seen[$componentGid])) {
                    $used[$componentGid] = true;
                    $seen[$componentGid] = true;
                    $stack[] = $componentGid;
                }
                $pos += 4;
                // Skip args (variable size; see OpenType spec for bit-flags layout).
                // ARG_1_AND_2_ARE_WORDS (0x01) → +4 bytes; else +2.
                $pos += ($flags & 0x01) ? 4 : 2;
                if ($flags & 0x08) {
                    $pos += 2; // WE_HAVE_A_SCALE
                } elseif ($flags & 0x40) {
                    $pos += 4; // WE_HAVE_AN_X_AND_Y_SCALE
                } elseif ($flags & 0x80) {
                    $pos += 8; // WE_HAVE_A_TWO_BY_TWO
                }
                if (! ($flags & 0x20)) { // MORE_COMPONENTS
                    break;
                }
            }
        }
    }

    /**
     * Build new glyf bytes and corresponding loca offsets.
     *
     * Used glyphs → copy outline bytes from original glyf.
     * Unused → 0-byte entry (loca[i] == loca[i+1]).
     *
     * @param  list<int>  $locaOffsets
     * @param  array<int, bool>  $used
     * @return array{0: string, 1: list<int>}  newGlyf bytes + new loca offsets
     */
    private function buildSubsetGlyf(
        string $sourceBytes,
        int $glyfBaseOffset,
        array $locaOffsets,
        array $used,
        ?VariableInstance $variableInstance = null,
    ): array {
        $newGlyf = '';
        $newOffsets = [];
        $cursor = 0;
        $numGlyphs = count($locaOffsets) - 1;
        for ($gid = 0; $gid < $numGlyphs; $gid++) {
            $newOffsets[] = $cursor;
            if (! isset($used[$gid])) {
                continue; // 0-byte entry
            }
            $start = $glyfBaseOffset + $locaOffsets[$gid];
            $length = $locaOffsets[$gid + 1] - $locaOffsets[$gid];
            if ($length === 0) {
                continue;
            }
            $glyphBytes = substr($sourceBytes, $start, $length);
            // Apply variation deltas if an instance is given.
            if ($variableInstance !== null) {
                $glyphBytes = $variableInstance->transformGlyph($gid, $glyphBytes);
            }
            // Pad to 2-byte alignment (TTF requirement for glyf entries).
            if (strlen($glyphBytes) % 2 !== 0) {
                $glyphBytes .= "\x00";
            }
            $newGlyf .= $glyphBytes;
            $cursor += strlen($glyphBytes);
        }
        $newOffsets[] = $cursor; // sentinel

        return [$newGlyf, $newOffsets];
    }

    /**
     * Packs loca offsets back to binary (format 0 = uint16×2, format 1 = uint32).
     *
     * @param  list<int>  $offsets
     */
    private function packLoca(array $offsets, int $format): string
    {
        $out = '';
        foreach ($offsets as $offset) {
            if ($format === 0) {
                $out .= pack('n', $offset / 2);
            } else {
                $out .= pack('N', $offset);
            }
        }

        return $out;
    }

    /**
     * Emit final TTF with replaced glyf + loca tables. All other tables
     * are copied bytes-as-is.
     *
     * Table directory: each entry is 16 bytes (tag + checksum + offset + length).
     * Tables are aligned on a 4-byte boundary in the file.
     *
     * @param  array<string, array{offset: int, length: int, tag: string}>  $tables
     */
    private function buildOutputTtf(string $sourceBytes, array $tables, string $newGlyf, string $newLoca): string
    {
        // Tables not needed by a PDF reader — we do not embed them, which
        // significantly reduces the final bundle:
        //  - GPOS/GSUB/GDEF — kerning/ligatures/glyph classes. PDF readers
        //    do not apply them (kerning is done by the caller via the
        //    TJ-operator). They are parsed OUTSIDE the subset, at
        //    TextMeasurer time.
        //  - DSIG — digital signature. Not needed.
        //  - kern (legacy) — same as GPOS kerning; not needed for PDF.
        //  - hdmx, VDMX, vhea, vmtx — horizontal/vertical metrics specific
        //    to screen rendering. Not needed by PDF readers.
        //
        // cmap and name are kept — formally needed for TTF validity
        // (some tools require them, plus our own parser).
        // Variation tables are dropped in the subset — outlines are
        // frozen to the specific instance (or default if no variation
        // applied). PDF readers cannot apply variations dynamically; they
        // expect static glyph outlines in the embedded font.
        $dropTables = ['GPOS', 'GSUB', 'GDEF', 'DSIG',
            'kern', 'hdmx', 'VDMX', 'vhea', 'vmtx', 'BASE', 'JSTF',
            'LTSH', 'PCLT', 'gasp', 'FFTM',
            'fvar', 'avar', 'gvar', 'HVAR', 'MVAR', 'VVAR',
            'CFF2', 'STAT', 'cvar'];

        $newTableData = [];
        foreach ($tables as $tag => $info) {
            if (in_array(trim($tag), $dropTables, true)) {
                continue;
            }
            $bytes = match ($tag) {
                'glyf' => $newGlyf,
                'loca' => $newLoca,
                default => substr($sourceBytes, $info['offset'], $info['length']),
            };
            $newTableData[$tag] = $bytes;
        }

        $numTables = count($newTableData);

        // Compute searchRange / entrySelector / rangeShift for header.
        $entrySelector = (int) floor(log($numTables, 2));
        $searchRange = (1 << $entrySelector) * 16;
        $rangeShift = $numTables * 16 - $searchRange;

        // Header: scaler (4) + numTables (2) + 3 × uint16 = 12 bytes.
        // Format N (uint32) + n4 (4 × uint16) = 4 + 8 = 12 bytes total.
        $header = pack('Nn4', 0x00010000, $numTables, $searchRange, $entrySelector, $rangeShift);

        // Build table directory + table data.
        $directorySize = $numTables * 16;
        $tableDataOffset = 12 + $directorySize;

        $directory = '';
        $tableData = '';
        $currentOffset = $tableDataOffset;
        foreach ($newTableData as $tag => $bytes) {
            $length = strlen($bytes);
            // Checksum: sum of 32-bit big-endian dwords.
            $checksum = $this->tableChecksum($bytes);

            $directory .= str_pad($tag, 4, ' ').pack('N3', $checksum, $currentOffset, $length);

            $tableData .= $bytes;
            // Padding to 4-byte alignment.
            $pad = (4 - ($length % 4)) % 4;
            if ($pad > 0) {
                $tableData .= str_repeat("\x00", $pad);
            }
            $currentOffset += $length + $pad;
        }

        return $header.$directory.$tableData;
    }

    /**
     * Sum of big-endian uint32 values (zero-padded if necessary).
     */
    private function tableChecksum(string $bytes): int
    {
        $pad = (4 - (strlen($bytes) % 4)) % 4;
        if ($pad > 0) {
            $bytes .= str_repeat("\x00", $pad);
        }
        $sum = 0;
        $numDwords = strlen($bytes) / 4;
        for ($i = 0; $i < $numDwords; $i++) {
            $sum = ($sum + unpack('N', substr($bytes, $i * 4, 4))[1]) & 0xFFFFFFFF;
        }

        return $sum;
    }
}
