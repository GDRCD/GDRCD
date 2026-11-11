<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * Minimal TrueType Font file parser.
 *
 * Parses only what is needed for PDF font embedding:
 *  - Table directory (locate offsets of all tables)
 *  - `head`  → unitsPerEm, xMin/yMin/xMax/yMax (for FontBBox)
 *  - `hhea`  → ascent/descent, numberOfHMetrics
 *  - `maxp` → numGlyphs
 *  - `hmtx` → advance widths array
 *  - `cmap` → character-to-glyph-id mapping (format 4 + format 12)
 *  - `name` → PostScript name (for /BaseFont)
 *  - `OS/2` → CapHeight (optional), italic angle hints
 *  - `post` → italicAngle, isFixedPitch
 *
 * Does NOT parse: glyf/loca/cvt/fpgm/prep (these contain glyph outlines +
 * hinting and are embedded in the PDF as an opaque FontFile2 stream).
 *
 * Reference: ISO/IEC 14496-22 + Apple TrueType Reference Manual.
 */
final class TtfFile
{
    /**
     * Scaler type magic. 0x00010000 = TTF (Windows/Adobe).
     * 'true' = Apple TTF (old). 'OTTO' = OTF with CFF (NOT supported).
     */
    private const SCALER_TTF = 0x00010000;

    private const SCALER_APPLE = 'true';

    private const SCALER_OTF_CFF = 'OTTO';

    private readonly BinaryReader $reader;

    /** @var array<string, array{offset: int, length: int}>  tag → metadata */
    private array $tables = [];

    private int $unitsPerEm;

    private int $numGlyphs;

    /** @var list<int>  bbox: [xMin, yMin, xMax, yMax] in FUnits */
    private array $bbox;

    private int $ascent;

    private int $descent;

    /** @var list<int>  index = glyphId; value = advance width in FUnits */
    private array $advanceWidths;

    /** @var array<int, int>  unicodeCodepoint → glyphId */
    private array $cmap;

    private string $postScriptName;

    private int $italicAngle;

    private bool $isFixedPitch;

    public function __construct(
        private readonly string $bytes,
    ) {
        $this->reader = new BinaryReader($bytes);
        $this->parseTableDirectory();
        $this->parseHead();
        $this->parseMaxp();
        $this->parseHhea();
        $this->parseHmtx();
        $this->parseCmap();
        $this->parseName();
        $this->parsePost();
    }

    public static function fromFile(string $path): self
    {
        if (! is_readable($path)) {
            throw new \InvalidArgumentException("Cannot read TTF: $path");
        }

        return new self((string) file_get_contents($path));
    }

    public function rawBytes(): string
    {
        return $this->bytes;
    }

    public function unitsPerEm(): int
    {
        return $this->unitsPerEm;
    }

    public function numGlyphs(): int
    {
        return $this->numGlyphs;
    }

    /** @return list<int> [xMin, yMin, xMax, yMax] */
    public function bbox(): array
    {
        return $this->bbox;
    }

    public function ascent(): int
    {
        return $this->ascent;
    }

    public function descent(): int
    {
        return $this->descent;
    }

    public function postScriptName(): string
    {
        return $this->postScriptName;
    }

    public function italicAngle(): int
    {
        return $this->italicAngle;
    }

    public function isFixedPitch(): bool
    {
        return $this->isFixedPitch;
    }

    /**
     * Lazy-parsed kerning table from GPOS (cached). Null if GPOS is absent
     * or does not contain pair-adjustment lookups.
     */
    private ?KerningTable $kerningTable = null;

    private bool $kerningParsed = false;

    public function kerningTable(): ?KerningTable
    {
        if ($this->kerningParsed) {
            return $this->kerningTable;
        }
        $this->kerningParsed = true;
        $gposInfo = $this->tableInfo('GPOS');
        if ($gposInfo === null) {
            return null;
        }
        $table = (new GposReader)->read($this->bytes, $gposInfo);
        if (! $table->isEmpty()) {
            $this->kerningTable = $table;
        }

        return $this->kerningTable;
    }

    /**
     * Lazy-parsed ligature substitutions (cached) from GSUB. Null if
     * GSUB is absent or does not contain 'liga' feature lookups.
     */
    private ?LigatureSubstitutions $ligatures = null;

    private bool $ligaturesParsed = false;

    public function ligatures(): ?LigatureSubstitutions
    {
        if ($this->ligaturesParsed) {
            return $this->ligatures;
        }
        $this->ligaturesParsed = true;
        $gsubInfo = $this->tableInfo('GSUB');
        if ($gsubInfo === null) {
            return null;
        }
        $subs = (new GsubReader)->read($this->bytes, $gsubInfo);
        if (! $subs->isEmpty()) {
            $this->ligatures = $subs;
        }

        return $this->ligatures;
    }

    /** @var array<string, ?SingleSubstitutions> */
    private array $singleSubstByFeature = [];

    /**
     * Read GSUB Type 1 Single Substitution for an arbitrary feature tag
     * (e.g. 'rphf' for Indic reph, 'half'/'init'/'medi'/'fina' for
     * half-forms and Arabic positional forms).
     */
    public function singleSubstitutionsForFeature(string $featureTag): ?SingleSubstitutions
    {
        if (array_key_exists($featureTag, $this->singleSubstByFeature)) {
            return $this->singleSubstByFeature[$featureTag];
        }
        $gsubInfo = $this->tableInfo('GSUB');
        if ($gsubInfo === null) {
            return $this->singleSubstByFeature[$featureTag] = null;
        }
        $result = (new GsubReader)->readByFeature($this->bytes, $gsubInfo, $featureTag);

        return $this->singleSubstByFeature[$featureTag] = $result['single']->isEmpty() ? null : $result['single'];
    }

    /**
     * Resolve Unicode codepoint → glyph ID. Returns 0 (.notdef) if char
     * is not covered by the font.
     */
    public function glyphIdForChar(int $codepoint): int
    {
        return $this->cmap[$codepoint] ?? 0;
    }

    public function advanceWidth(int $glyphId): int
    {
        return $this->advanceWidths[$glyphId] ?? 0;
    }

    /**
     * Access to the raw-offset table (for subclasses / debug).
     *
     * @return array{offset: int, length: int}|null
     */
    public function tableInfo(string $tag): ?array
    {
        return $this->tables[$tag] ?? null;
    }

    /**
     * Detect OpenType variable font (presence of `fvar` table).
     */
    public function isVariable(): bool
    {
        return $this->tableInfo('fvar') !== null;
    }

    /** @var array{axes:list<array{tag:string,min:float,default:float,max:float,nameId:int,flags:int}>, instances:list<array{nameId:int,coordinates:array<string,float>,postScriptNameId:?int,flags:int}>}|null */
    private ?array $fvarParsed = null;

    /**
     * Variation axes definitions from fvar table.
     *
     * Returns a list of axes with tag (4-char like "wght", "wdth", "ital",
     * "slnt", "opsz" or custom), min/default/max values, and nameId (for
     * human-readable name lookup via `name` table).
     *
     * @return list<array{tag:string,min:float,default:float,max:float,nameId:int,flags:int}>
     */
    public function variationAxes(): array
    {
        return $this->parseFvar()['axes'];
    }

    /**
     * Named instances (predefined coordinate combinations) from fvar.
     *
     * Each instance has a subfamily nameId (e.g., "Light", "Regular",
     * "Bold"), a coordinates map axisTag → value, and an optional
     * postScript nameId.
     *
     * @return list<array{nameId:int,coordinates:array<string,float>,postScriptNameId:?int,flags:int}>
     */
    public function namedInstances(): array
    {
        return $this->parseFvar()['instances'];
    }

    /** @return array{axes:list<array<string,mixed>>, instances:list<array<string,mixed>>} */
    private function parseFvar(): array
    {
        if ($this->fvarParsed !== null) {
            return $this->fvarParsed;
        }
        $info = $this->tableInfo('fvar');
        if ($info === null) {
            return $this->fvarParsed = ['axes' => [], 'instances' => []];
        }

        return $this->fvarParsed = (new FvarReader)->read($this->bytes, $info);
    }

    /**
     * Lookup human-readable name by nameID through the `name` table.
     * Returns null if nameID is not found.
     */
    public function nameById(int $nameId): ?string
    {
        return $this->namesById[$nameId] ?? null;
    }

    /** Lazy avar/HVAR/MVAR parsers. */
    private ?AvarReader $avar = null;

    private bool $avarParsed = false;

    private ?HvarReader $hvar = null;

    private bool $hvarParsed = false;

    private ?MvarReader $mvar = null;

    private bool $mvarParsed = false;

    public function avar(): ?AvarReader
    {
        if ($this->avarParsed) {
            return $this->avar;
        }
        $this->avarParsed = true;
        $info = $this->tableInfo('avar');
        if ($info !== null) {
            $this->avar = AvarReader::read($this->bytes, $info);
        }

        return $this->avar;
    }

    public function hvar(): ?HvarReader
    {
        if ($this->hvarParsed) {
            return $this->hvar;
        }
        $this->hvarParsed = true;
        $info = $this->tableInfo('HVAR');
        if ($info !== null) {
            $this->hvar = HvarReader::read($this->bytes, $info);
        }

        return $this->hvar;
    }

    public function mvar(): ?MvarReader
    {
        if ($this->mvarParsed) {
            return $this->mvar;
        }
        $this->mvarParsed = true;
        $info = $this->tableInfo('MVAR');
        if ($info !== null) {
            $this->mvar = MvarReader::read($this->bytes, $info);
        }

        return $this->mvar;
    }

    /** Lazy gvar parser. */
    private ?GvarReader $gvar = null;

    private bool $gvarParsed = false;

    public function gvar(): ?GvarReader
    {
        if ($this->gvarParsed) {
            return $this->gvar;
        }
        $this->gvarParsed = true;
        $info = $this->tableInfo('gvar');
        if ($info !== null) {
            $this->gvar = GvarReader::read($this->bytes, $info);
        }

        return $this->gvar;
    }

    /**
     * Convert user-space axis coords to normalized -1..+1 space. Applies
     * linear default normalization, then avar piecewise-linear remap if
     * an avar table is present.
     *
     * @param  array<string, float>  $userCoords  axis tag → user value
     * @return array<int, float>  axis index → normalized coord (-1..+1)
     */
    public function normalizeCoordinates(array $userCoords): array
    {
        $axes = $this->variationAxes();
        $norm = [];
        $avar = $this->avar();
        foreach ($axes as $i => $axis) {
            $val = $userCoords[$axis['tag']] ?? $axis['default'];
            // Clamp to [min, max].
            $val = max($axis['min'], min($axis['max'], $val));
            // Linear default normalization.
            if ($val < $axis['default']) {
                $n = $axis['default'] - $axis['min'];
                $linear = $n > 0 ? ($val - $axis['default']) / $n : 0.0;
            } elseif ($val > $axis['default']) {
                $n = $axis['max'] - $axis['default'];
                $linear = $n > 0 ? ($val - $axis['default']) / $n : 0.0;
            } else {
                $linear = 0.0;
            }
            // Apply avar remap.
            $norm[$i] = $avar !== null ? $avar->map($i, $linear) : $linear;
        }

        return $norm;
    }

    /**
     * Interpolated advance width for a glyph under given axis coords.
     * Falls back to the default advanceWidth if the font is not variable
     * or HVAR is absent.
     *
     * @param  array<string, float>  $userCoords
     */
    public function advanceWidthForInstance(int $glyphId, array $userCoords): int
    {
        $default = $this->advanceWidth($glyphId);
        $hvar = $this->hvar();
        if ($hvar === null) {
            return $default;
        }
        $norm = $this->normalizeCoordinates($userCoords);
        $delta = $hvar->advanceDelta($glyphId, $norm);

        return (int) round($default + $delta);
    }

    /**
     * Interpolated font metric (e.g., 'asc ', 'desc', 'cpht'). Returns
     * the base metric value plus MVAR delta, or the base value if no MVAR.
     *
     * @param  array<string, float>  $userCoords
     */
    public function metricForInstance(string $tag, int $baseValue, array $userCoords): int
    {
        $mvar = $this->mvar();
        if ($mvar === null) {
            return $baseValue;
        }
        $norm = $this->normalizeCoordinates($userCoords);
        $delta = $mvar->metricDelta($tag, $norm);

        return (int) round($baseValue + $delta);
    }

    /**
     * Read header + table directory.
     * Header: scaler type (4) + numTables (2) + 3 × uint16 = 12 bytes.
     * Each entry: tag (4) + checksum (4) + offset (4) + length (4) = 16.
     */
    private function parseTableDirectory(): void
    {
        $this->reader->seek(0);
        $scaler = $this->reader->readUInt32();

        // 0x00010000 for standard TTF, 'OTTO' for CFF-based OTF.
        if ($scaler !== self::SCALER_TTF
            && substr($this->bytes, 0, 4) !== self::SCALER_APPLE) {
            if (substr($this->bytes, 0, 4) === self::SCALER_OTF_CFF) {
                throw new \RuntimeException('CFF-based OTF fonts not supported in POC; need TTF outline-based.');
            }
            throw new \RuntimeException(sprintf('Unknown font scaler 0x%08X', $scaler));
        }

        $numTables = $this->reader->readUInt16();
        $this->reader->skip(6); // searchRange + entrySelector + rangeShift — not needed

        for ($i = 0; $i < $numTables; $i++) {
            $tag = $this->reader->readTag();
            $this->reader->skip(4); // checksum
            $offset = $this->reader->readUInt32();
            $length = $this->reader->readUInt32();
            $this->tables[$tag] = ['offset' => $offset, 'length' => $length];
        }
    }

    /**
     * `head` table — base font metrics.
     * Offsets (from start of table):
     *   0..3   majorVersion, minorVersion (Fixed)
     *   4..7   fontRevision (Fixed)
     *   8..11  checkSumAdjustment
     *   12..15 magicNumber (must be 0x5F0F3CF5)
     *   16..17 flags
     *   18..19 unitsPerEm
     *   20..35 created/modified (Date64, 16 bytes total)
     *   36..37 xMin
     *   38..39 yMin
     *   40..41 xMax
     *   42..43 yMax
     *   ...etc
     */
    private function parseHead(): void
    {
        $offset = $this->requireTable('head');
        $this->reader->seek($offset + 18);
        $this->unitsPerEm = $this->reader->readUInt16();
        $this->reader->seek($offset + 36);
        $this->bbox = [
            $this->reader->readInt16(),
            $this->reader->readInt16(),
            $this->reader->readInt16(),
            $this->reader->readInt16(),
        ];
    }

    /**
     * `maxp` — contains numGlyphs (offset 4, uint16).
     */
    private function parseMaxp(): void
    {
        $offset = $this->requireTable('maxp');
        $this->reader->seek($offset + 4);
        $this->numGlyphs = $this->reader->readUInt16();
    }

    /**
     * `hhea`:
     *   4..5   ascender (int16)
     *   6..7   descender (int16)
     *   34..35 numberOfHMetrics (uint16)
     */
    private int $numHMetrics;

    private function parseHhea(): void
    {
        $offset = $this->requireTable('hhea');
        $this->reader->seek($offset + 4);
        $this->ascent = $this->reader->readInt16();
        $this->descent = $this->reader->readInt16();
        $this->reader->seek($offset + 34);
        $this->numHMetrics = $this->reader->readUInt16();
    }

    /**
     * `hmtx` — array of longHorMetric (advance width + lsb) for the first
     * numHMetrics entries plus an optional lsb-only array for the rest.
     * Glyphs with index >= numHMetrics use the last-known advance width.
     */
    private function parseHmtx(): void
    {
        $offset = $this->requireTable('hmtx');
        $this->reader->seek($offset);
        $this->advanceWidths = [];

        $lastWidth = 0;
        for ($i = 0; $i < $this->numHMetrics; $i++) {
            $lastWidth = $this->reader->readUInt16();
            $this->reader->skip(2); // lsb (signed, not used)
            $this->advanceWidths[$i] = $lastWidth;
        }
        // Remaining glyphs — lsb only, width = lastWidth.
        for ($i = $this->numHMetrics; $i < $this->numGlyphs; $i++) {
            $this->advanceWidths[$i] = $lastWidth;
        }
    }

    /**
     * Vertical metrics support.
     *
     * `vhea` table (vertical header) — analog of hhea for vertical writing.
     * Offsets: 4..5 ascent, 6..7 descent, 34..35 numOfLongVerMetrics.
     *
     * `vmtx` table — analog of hmtx with advance height + topSideBearing
     * pairs. Used for CJK vertical writing /WMode 1.
     */
    /** @var array<int, int>|null Lazy-parsed advance heights (vertical advance per glyph) */
    private ?array $advanceHeights = null;

    public function advanceHeight(int $glyphId): ?int
    {
        if ($this->advanceHeights === null) {
            $this->parseVmtx();
        }

        return $this->advanceHeights[$glyphId] ?? null;
    }

    public function hasVerticalMetrics(): bool
    {
        return $this->tableInfo('vhea') !== null && $this->tableInfo('vmtx') !== null;
    }

    private function parseVmtx(): void
    {
        $this->advanceHeights = [];
        if (! $this->hasVerticalMetrics()) {
            return;
        }
        // Read vhea for numOfLongVerMetrics.
        $vheaOffset = $this->requireTable('vhea');
        $this->reader->seek($vheaOffset + 34);
        $numVMetrics = $this->reader->readUInt16();

        $vmtxOffset = $this->requireTable('vmtx');
        $this->reader->seek($vmtxOffset);
        $lastHeight = 0;
        for ($i = 0; $i < $numVMetrics; $i++) {
            $lastHeight = $this->reader->readUInt16();
            $this->reader->skip(2); // topSideBearing
            $this->advanceHeights[$i] = $lastHeight;
        }
        for ($i = $numVMetrics; $i < $this->numGlyphs; $i++) {
            $this->advanceHeights[$i] = $lastHeight;
        }
    }

    /**
     * `cmap` — character-to-glyph mapping. Contains several subtables;
     * we look for format 12 (full Unicode) first, then format 4 (BMP).
     *
     * cmap header:
     *   0..1   version (uint16)
     *   2..3   numTables (uint16)
     *   then numTables entries of 8 bytes each:
     *     0..1 platformID
     *     2..3 encodingID
     *     4..7 offset (from start of cmap table)
     *
     * Preferences:
     *   - Windows / Unicode full (platform 3, encoding 10) — format 12
     *   - Windows / BMP (platform 3, encoding 1) — format 4
     *   - Unicode / BMP (platform 0, encoding 3) — format 4
     */
    private function parseCmap(): void
    {
        $cmapStart = $this->requireTable('cmap');
        $this->reader->seek($cmapStart);
        $this->reader->skip(2); // version
        $numSubtables = $this->reader->readUInt16();

        $subtables = []; // priority → offset
        for ($i = 0; $i < $numSubtables; $i++) {
            $platformId = $this->reader->readUInt16();
            $encodingId = $this->reader->readUInt16();
            $subtableOffset = $cmapStart + $this->reader->readUInt32();
            $priority = $this->cmapSubtablePriority($platformId, $encodingId);
            if ($priority !== null) {
                $subtables[$priority] = $subtableOffset;
            }
        }

        if ($subtables === []) {
            throw new \RuntimeException('No usable cmap subtable found in TTF.');
        }
        // Merge all usable subtables, lowest priority first, so the
        // highest-priority mapping wins per codepoint. A format 12 subtable
        // is not guaranteed to be a superset of the format 4 one: e.g.
        // DroidSansFallback maps Latin only in format 4 and CJK only in
        // format 12 — picking a single subtable loses half the font.
        krsort($subtables);
        $merged = [];
        $parsedAny = false;
        foreach ($subtables as $offset) {
            $this->reader->seek($offset);
            $format = $this->reader->readUInt16();
            $parsed = match ($format) {
                4 => $this->parseCmapFormat4($offset),
                12 => $this->parseCmapFormat12($offset),
                default => null, // ignore other formats if a usable one exists
            };
            if ($parsed !== null) {
                $merged = $parsed + $merged;
                $parsedAny = true;
            }
        }
        if (! $parsedAny) {
            throw new \RuntimeException('No supported cmap subtable format found in TTF.');
        }
        $this->cmap = $merged;
    }

    private function cmapSubtablePriority(int $platformId, int $encodingId): ?int
    {
        // Lower = higher priority.
        if ($platformId === 3 && $encodingId === 10) {
            return 1; // Windows / Unicode full (UCS-4)
        }
        if ($platformId === 0 && $encodingId === 4) {
            return 2; // Unicode / full
        }
        if ($platformId === 3 && $encodingId === 1) {
            return 3; // Windows / Unicode BMP
        }
        if ($platformId === 0 && ($encodingId === 3 || $encodingId === 6)) {
            return 4; // Unicode / BMP
        }

        return null; // not used (Macintosh, Symbol, etc.)
    }

    /**
     * cmap format 4 — segment mapping for BMP (U+0000..U+FFFF).
     *
     * Schema:
     *   0..1   format (= 4)
     *   2..3   length
     *   4..5   language
     *   6..7   segCountX2 (2 × segCount)
     *   8..9   searchRange / entrySelector / rangeShift (skip 6 bytes)
     *   14..   endCode array (segCount × uint16)
     *   ...    reservedPad (uint16)
     *   ...    startCode array (segCount × uint16)
     *   ...    idDelta array (segCount × int16)
     *   ...    idRangeOffset array (segCount × uint16)
     *   ...    glyphIdArray
     *
     * Mapping algorithm:
     *   for each char c:
     *     find segment i where startCode[i] <= c <= endCode[i]
     *     if idRangeOffset[i] == 0:
     *       glyphId = (c + idDelta[i]) mod 65536
     *     else:
     *       glyphId = glyphIdArray[idRangeOffset[i]/2 + (c - startCode[i]) + i - segCount]
     *
     * @return array<int, int>
     */
    private function parseCmapFormat4(int $subtableOffset): array
    {
        $this->reader->seek($subtableOffset + 6);
        $segCountX2 = $this->reader->readUInt16();
        $segCount = $segCountX2 / 2;
        $this->reader->skip(6); // searchRange + entrySelector + rangeShift

        $endCode = [];
        for ($i = 0; $i < $segCount; $i++) {
            $endCode[$i] = $this->reader->readUInt16();
        }
        $this->reader->skip(2); // reservedPad

        $startCode = [];
        for ($i = 0; $i < $segCount; $i++) {
            $startCode[$i] = $this->reader->readUInt16();
        }

        $idDelta = [];
        for ($i = 0; $i < $segCount; $i++) {
            $idDelta[$i] = $this->reader->readInt16();
        }

        $idRangeOffsetStart = $this->reader->tell();
        $idRangeOffset = [];
        for ($i = 0; $i < $segCount; $i++) {
            $idRangeOffset[$i] = $this->reader->readUInt16();
        }

        $cmap = [];
        for ($i = 0; $i < $segCount; $i++) {
            $start = $startCode[$i];
            $end = $endCode[$i];
            // Last segment is always 0xFFFF..0xFFFF mapping to 0. Skip.
            if ($start === 0xFFFF && $end === 0xFFFF) {
                continue;
            }
            for ($c = $start; $c <= $end; $c++) {
                if ($idRangeOffset[$i] === 0) {
                    $glyphId = ($c + $idDelta[$i]) & 0xFFFF;
                } else {
                    $glyphIndexPos = $idRangeOffsetStart + $i * 2
                        + $idRangeOffset[$i]
                        + ($c - $start) * 2;
                    $glyphId = $this->reader->peekUInt16($glyphIndexPos);
                    if ($glyphId !== 0) {
                        $glyphId = ($glyphId + $idDelta[$i]) & 0xFFFF;
                    }
                }
                if ($glyphId !== 0) {
                    $cmap[$c] = $glyphId;
                }
            }
        }

        return $cmap;
    }

    /**
     * cmap format 12 — segmented mapping for full UCS-4 (with supplementary
     * planes outside BMP).
     *
     * Schema:
     *   0..1   format (= 12)
     *   2..3   reserved
     *   4..7   length (uint32)
     *   8..11  language (uint32)
     *   12..15 numGroups (uint32)
     *   then numGroups × {startCharCode: uint32, endCharCode: uint32,
     *                     startGlyphID: uint32}
     *
     * @return array<int, int>
     */
    private function parseCmapFormat12(int $subtableOffset): array
    {
        $this->reader->seek($subtableOffset + 12);
        $numGroups = $this->reader->readUInt32();

        $cmap = [];
        for ($g = 0; $g < $numGroups; $g++) {
            $start = $this->reader->readUInt32();
            $end = $this->reader->readUInt32();
            $startGlyph = $this->reader->readUInt32();
            for ($c = $start, $glyph = $startGlyph; $c <= $end; $c++, $glyph++) {
                $cmap[$c] = $glyph;
            }
        }

        return $cmap;
    }

    /**
     * `name` table — contains metadata strings (font family, postscript name).
     *
     * Header:
     *   0..1  format (usually 0)
     *   2..3  count
     *   4..5  stringOffset (from start of name table)
     *   then `count` records of 12 bytes each:
     *     0..1 platformID
     *     2..3 encodingID
     *     4..5 languageID
     *     6..7 nameID
     *     8..9 length
     *     10..11 offset
     *
     * We look for nameID 6 (PostScript name). Preference — Windows
     * (platformID 3) with Unicode (UTF-16BE).
     */
    /** @var array<int, string> nameId → decoded UTF-8 string (best-quality variant). */
    private array $namesById = [];

    private function parseName(): void
    {
        $info = $this->tableInfo('name');
        if ($info === null) {
            $this->postScriptName = 'UnknownFont';

            return;
        }
        $nameStart = $info['offset'];
        $this->reader->seek($nameStart + 2);
        $count = $this->reader->readUInt16();
        $stringOffset = $this->reader->readUInt16();

        // Collect all name records, prefer Windows platform=3 over Macintosh=1.
        $best = null;
        for ($i = 0; $i < $count; $i++) {
            $platformId = $this->reader->readUInt16();
            $encodingId = $this->reader->readUInt16();
            $this->reader->skip(2); // languageID
            $nameId = $this->reader->readUInt16();
            $length = $this->reader->readUInt16();
            $offset = $this->reader->readUInt16();
            $absOffset = $nameStart + $stringOffset + $offset;
            $rawString = substr($this->bytes, $absOffset, $length);
            $value = match ($platformId) {
                3 => mb_convert_encoding($rawString, 'UTF-8', 'UTF-16BE'),
                1 => $rawString,
                default => $rawString,
            };
            if ($value === '' || $value === false) {
                continue;
            }
            // Store ALL name IDs for variable-font instance/axis lookup.
            // Prefer Windows (platform 3) — overwrites Mac entry if both exist.
            if (! isset($this->namesById[$nameId]) || $platformId === 3) {
                $this->namesById[$nameId] = $value;
            }
            // PostScript name = nameId 6.
            if ($nameId === 6 && ($platformId === 3 || $best === null)) {
                $best = $value;
            }
        }

        $this->postScriptName = $best ?? 'UnknownFont';
    }

    /**
     * `post` — italicAngle (Fixed 16.16, offset 4) and isFixedPitch
     * (uint32, offset 12, non-zero = monospace).
     */
    private function parsePost(): void
    {
        $info = $this->tableInfo('post');
        if ($info === null) {
            $this->italicAngle = 0;
            $this->isFixedPitch = false;

            return;
        }
        $offset = $info['offset'];
        $this->reader->seek($offset + 4);
        // italicAngle: Fixed 16.16. High 16 bits are the integer part.
        $italicRaw = $this->reader->readInt32();
        $this->italicAngle = $italicRaw >> 16;

        $this->reader->seek($offset + 12);
        $this->isFixedPitch = $this->reader->readUInt32() !== 0;
    }

    private function requireTable(string $tag): int
    {
        $info = $this->tableInfo($tag);
        if ($info === null) {
            throw new \RuntimeException("Required TTF table '$tag' not found.");
        }

        return $info['offset'];
    }
}
