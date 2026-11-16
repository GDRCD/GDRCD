<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

use Dskripchenko\PhpPdf\Font\Ttf\TtfFile;

/**
 * Builds a Type0 composite font in PDF from TtfFile.
 *
 * PDF font object structure (ISO 32000-1 §9.7):
 *
 *   Font (Type0)
 *     /BaseFont /<PostScriptName>
 *     /Encoding /Identity-H        2-byte glyph IDs as character codes
 *     /DescendantFonts [<<CIDFontType2>>]
 *     /ToUnicode <<CMap stream>>   for copy-paste correctness
 *
 *   CIDFontType2 (descendant)
 *     /BaseFont /<PostScriptName>
 *     /CIDSystemInfo << /Registry (Adobe) /Ordering (Identity) /Supplement 0 >>
 *     /CIDToGIDMap /Identity        CID == glyph ID directly
 *     /FontDescriptor <<...>>
 *     /W [<gid> [<width>]]
 *
 *   FontDescriptor
 *     /FontName /<PostScriptName>
 *     /Flags <integer>              bit-flags: serif, fixed-pitch, italic, etc.
 *     /FontBBox [xMin yMin xMax yMax]
 *     /Ascent / /Descent / /CapHeight / /ItalicAngle / /StemV
 *     /FontFile2 <<embedded TTF stream>>
 *
 *   FontFile2 stream
 *     /Length <compressed length>
 *     /Length1 <original TTF length>
 *
 * Encoding text: 2-byte big-endian glyph IDs in hex string
 *   `<00480065006C006C006F>` for "Hello"
 *
 * Coords for widths: PDF convention is 1000/em. TTF FUnit to PDF unit
 * conversion: `PDF_width = TTF_width * 1000 / unitsPerEm`.
 */
final class PdfFont
{
    /**
     * glyphId to list of source codepoints (for ToUnicode CMap construction).
     * For regular glyphs, a single-element list. For ligature glyphs,
     * the list of codepoints of the source sequence (e.g., glyph "fi" -> ['f', 'i']).
     *
     * @var array<int, list<int>>
     */
    private array $usedGlyphs = [];

    /**
     * Per-Writer registration cache. Allows safe reuse of the same
     * PdfFont instance across multiple Documents - each writer gets its own
     * fontObjectId.
     *
     * @var \SplObjectStorage<Writer, int>
     */
    private \SplObjectStorage $writerRegistrations;

    /**
     * @var bool  Apply ligature substitutions from GSUB ('liga' feature).
     *            False disables them (for debug or fonts with problematic liga).
     */
    private bool $ligaturesEnabled = true;

    /**
     * @param  bool  $subset  If true, embed only used glyphs via
     *                        TtfSubsetter (typically from ~411KB to ~5-50KB).
     *                        If false, full TTF (backward-compat for
     *                        corner-cases where subsetting causes issues).
     */
    /**
     * @param  array<string, float>  $axes  Variable font axis coords
     *                                       (e.g., ['wght' => 700]). When embedding,
     *                                       glyph outlines are pre-interpolated
     *                                       and a static subset is emitted.
     *                                       Empty array = default instance.
     */
    public function __construct(
        private readonly TtfFile $ttf,
        private readonly bool $subset = true,
        private readonly array $axes = [],
        /**
         * Vertical writing mode for CJK text. When true:
         *  - CIDFontType2 dict gets /WMode 1
         *  - /W2 array emitted with vertical advance metrics from vmtx table
         *  - Caller is responsible for using vertical positioning in layout
         *
         * Font must have vhea+vmtx tables - check TtfFile::hasVerticalMetrics()
         * before setting verticalWriting=true.
         */
        private readonly bool $verticalWriting = false,
    ) {
        if ($verticalWriting && ! $ttf->hasVerticalMetrics()) {
            throw new \InvalidArgumentException(
                'Vertical writing requested but font lacks vhea+vmtx tables'
            );
        }
        $this->writerRegistrations = new \SplObjectStorage;
    }

    public function isVerticalWriting(): bool
    {
        return $this->verticalWriting;
    }

    /**
     * Explicitly clear per-Writer registration cache and used glyphs.
     * Use case: same PdfFont instance reused across many Documents where each
     * doc must have a fresh subset (containing only ITS glyphs).
     *
     * Without reset() usedGlyphs accumulates across docs (each subsequent doc
     * embeds a superset). With reset(), per-doc subset is accurate to that doc.
     */
    public function reset(): void
    {
        $this->usedGlyphs = [];
        $this->writerRegistrations = new \SplObjectStorage;
        $this->decodeCache = [];
    }

    /**
     * Registers the font with a Writer. Creates all necessary objects and
     * returns the Type0 font dict object ID - this ID is used in the page
     * Resources /Font dict.
     */
    public function registerWith(Writer $writer, bool $compressStreams = true): int
    {
        // per-Writer cache (instead of single $fontObjectId).
        if (isset($this->writerRegistrations[$writer])) {
            return $this->writerRegistrations[$writer];
        }

        // 1. Embed TTF binary as FontFile2 stream object. With FlateDecode
        //    font subsets are ~50-70% smaller (typical 30-70KB to 15-35KB).
        // variation instance, if font is variable and axes are set.
        $variableInstance = null;
        if ($this->axes !== [] && $this->ttf->isVariable()) {
            $variableInstance = new \Dskripchenko\PhpPdf\Font\Ttf\VariableInstance($this->ttf, $this->axes);
        }
        $fontBytes = $this->subset
            ? (new \Dskripchenko\PhpPdf\Font\Ttf\TtfSubsetter)->subset($this->ttf, array_keys($this->usedGlyphs), $variableInstance)
            : $this->ttf->rawBytes();
        if ($compressStreams) {
            $compressed = (string) gzcompress($fontBytes, 6);
            $fontFileObjId = $writer->addObject(sprintf(
                "<< /Length %d /Length1 %d /Filter /FlateDecode >>\nstream\n%s\nendstream",
                strlen($compressed),
                strlen($fontBytes),  // Length1 = un-compressed original size (PDF spec)
                $compressed,
            ));
        } else {
            $fontFileObjId = $writer->addObject(sprintf(
                "<< /Length %d /Length1 %d >>\nstream\n%s\nendstream",
                strlen($fontBytes),
                strlen($fontBytes),
                $fontBytes,
            ));
        }

        // 2. FontDescriptor.
        $descriptorBody = $this->buildFontDescriptor($fontFileObjId);
        $descriptorId = $writer->addObject($descriptorBody);

        // 3. CIDFontType2 descendant.
        $cidFontBody = $this->buildCIDFont($descriptorId);
        $cidFontId = $writer->addObject($cidFontBody);

        // 4. ToUnicode CMap (placeholder, filled later when usedGlyphs known).
        //    But for POC we register all glyphs used in content
        //    streams BEFORE registerWith(). See addUsedChar().
        $toUnicodeId = $writer->addObject($this->buildToUnicodeCMap());

        // 5. Top-level Type0 font dict.
        $type0Body = sprintf(
            '<< /Type /Font /Subtype /Type0 /BaseFont /%s '
            .'/Encoding /Identity-H /DescendantFonts [%d 0 R] '
            .'/ToUnicode %d 0 R >>',
            $this->ttf->postScriptName(),
            $cidFontId,
            $toUnicodeId,
        );
        $fontObjectId = $writer->addObject($type0Body);
        $this->writerRegistrations[$writer] = $fontObjectId;

        return $fontObjectId;
    }

    /**
     * Internal raw-access to TtfFile (for TextMeasurer and LineBreaker,
     * which should not parse TTF themselves).
     */
    public function ttf(): \Dskripchenko\PhpPdf\Font\Ttf\TtfFile
    {
        return $this->ttf;
    }

    /**
     * Check whether all codepoints in $text exist in the font cmap.
     * ASCII control chars and space are treated as supported. Used for Engine
     * font fallback chain - if main font lacks glyph, try next in chain.
     */
    public function supportsText(string $text): bool
    {
        if ($text === '') {
            return true;
        }
        $len = mb_strlen($text, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $ch = mb_substr($text, $i, 1, 'UTF-8');
            $cp = mb_ord($ch, 'UTF-8');
            if ($cp === false || $cp <= 32) {
                // Control/space - universally supported.
                continue;
            }
            if ($this->ttf->glyphIdForChar($cp) === 0) {
                return false;
            }
        }

        return true;
    }

    /** Enable Arabic contextual shaping (init/medi/fina/isol). */
    private bool $arabicShapingEnabled = true;

    /** Enable Unicode Bidi reordering (UAX 9). */
    private bool $bidiEnabled = true;

    /** Enable Indic pre-base matra reordering. */
    private bool $indicShapingEnabled = true;

    /**
     * Bounded LRU cache for decodeUtf8 results - TextMeasurer
     * (Engine layout) repeatedly calls on the same words while measuring
     * line-fits, justification, etc.
     *
     * @var array<string, list<array{cp: int, gid: int}>>
     */
    private array $decodeCache = [];

    private const DECODE_CACHE_MAX = 2048;

    /**
     * Disable Arabic shaping (for debug or if font handles
     * shaping itself via GSUB rather than Presentation Forms B).
     */
    public function disableArabicShaping(): void
    {
        $this->arabicShapingEnabled = false;
    }

    /**
     * Disable Bidi reordering. Useful when the caller provides
     * pre-ordered visual text (e.g., from external Bidi pipeline).
     */
    public function disableBidi(): void
    {
        $this->bidiEnabled = false;
    }

    /**
     * Disable Indic pre-base matra reordering.
     */
    public function disableIndicShaping(): void
    {
        $this->indicShapingEnabled = false;
    }

    /**
     * Decode UTF-8 into a list of (codepoint, glyphId) - low-level decoder.
     * Does NOT apply ligature substitution. Side-effect: does NOT accumulate
     * into usedGlyphs (that's done by shapedGlyphs/encodeText).
     *
     * Pipeline:
     *  1. UTF-8 to codepoints (logical order)
     *  2. ArabicShaper::shapeLogical - contextual shaping in logical order
     *  3. BidiAlgorithm::reorderCodepoints - UAX 9 visual reordering
     *  4. Cmap lookup per visual codepoint
     *
     * @return list<array{cp: int, gid: int}>
     */
    public function decodeUtf8(string $utf8): array
    {
        // Memo cache. TextMeasurer repeatedly calls decodeUtf8
        // on the same words during layout passes; without cache the pipeline
        // (utf8 to cps + Arabic/Indic shape + Bidi + cmap + rphf) runs
        // every time and blows memory. Bounded FIFO cap = 2048 entries.
        if (isset($this->decodeCache[$utf8])) {
            return $this->decodeCache[$utf8];
        }

        $cps = self::utf8ToCps($utf8);

        // Arabic contextual shaping in logical order.
        if ($this->arabicShapingEnabled && self::containsArabic($cps)) {
            $cps = \Dskripchenko\PhpPdf\Text\ArabicShaper::shapeLogical($cps);
        }

        $indicApplied = false;
        if ($this->indicShapingEnabled && self::containsIndic($cps)) {
            $cps = \Dskripchenko\PhpPdf\Text\IndicShaper::shape($cps);
            $indicApplied = true;
        }

        // Bidi reordering for visual display.
        if ($this->bidiEnabled && self::containsRtl($cps)) {
            $cps = \Dskripchenko\PhpPdf\Text\BidiAlgorithm::reorderCodepoints($cps);
        }

        // Detect reph positions BEFORE cmap (need codepoint
        // context: RA+virama preceded by non-virama -> reph candidate).
        $rphfPositions = $indicApplied ? self::detectRphPositions($cps) : [];

        $out = [];
        foreach ($cps as $cp) {
            $out[] = ['cp' => $cp, 'gid' => $this->ttf->glyphIdForChar($cp)];
        }

        // Apply GSUB 'rphf' single substitution to reph positions.
        if ($rphfPositions !== []) {
            $rphf = $this->ttf->singleSubstitutionsForFeature('rphf');
            if ($rphf !== null) {
                foreach ($rphfPositions as $idx => $_) {
                    if (isset($out[$idx]) && $rphf->has($out[$idx]['gid'])) {
                        $out[$idx]['gid'] = $rphf->substitute($out[$idx]['gid']);
                    }
                }
            }
        }

        // Bounded FIFO eviction: drop oldest entry when reached cap.
        if (count($this->decodeCache) >= self::DECODE_CACHE_MAX) {
            array_shift($this->decodeCache);
        }
        $this->decodeCache[$utf8] = $out;

        return $out;
    }

    /**
     * @param  list<int>  $cps
     * @return array<int, true>
     *
     * @internal exposed for unit testing of reph detection
     */
    public static function detectRphPositionsForTest(array $cps): array
    {
        return self::detectRphPositions($cps);
    }

    /**
     * Identify codepoint positions where RA + virama forms a reph.
     * After Indic shaping, reph clusters have RA + virama at end of syllable
     * (NOT at start). Detection: RA followed by virama, where previous cp
     * is NOT virama (excluding subscript-RA case "halant + RA").
     *
     * @param  list<int>  $cps
     * @return array<int, true>  Index of RA codepoint that should be rphf-substituted.
     */
    private static function detectRphPositions(array $cps): array
    {
        $positions = [];
        $n = count($cps);
        for ($i = 1; $i < $n - 1; $i++) {  // i > 0 (skip start) and i+1 < n (need following virama)
            if (\Dskripchenko\PhpPdf\Text\IndicShaper::isRA($cps[$i])
                && \Dskripchenko\PhpPdf\Text\IndicShaper::isVirama($cps[$i + 1])
                && ! \Dskripchenko\PhpPdf\Text\IndicShaper::isVirama($cps[$i - 1])
            ) {
                $positions[$i] = true;
            }
        }

        return $positions;
    }

    /** @param list<int> $cps */
    private static function containsIndic(array $cps): bool
    {
        foreach ($cps as $cp) {
            // Indic blocks: Devanagari (0900-097F), Bengali (0980-09FF),
            // Gurmukhi (0A00-0A7F), Gujarati (0A80-0AFF), Oriya (0B00-0B7F),
            // Tamil (0B80-0BFF), Telugu (0C00-0C7F), Kannada (0C80-0CFF),
            // Malayalam (0D00-0D7F), Sinhala (0D80-0DFF).
            if ($cp >= 0x0900 && $cp <= 0x0DFF) {
                return true;
            }
        }

        return false;
    }

    /** @param list<int> $cps */
    private static function containsRtl(array $cps): bool
    {
        foreach ($cps as $cp) {
            // Hebrew + Arabic blocks.
            if (($cp >= 0x0590 && $cp <= 0x08FF)
                || ($cp >= 0xFB1D && $cp <= 0xFDFF)
                || ($cp >= 0xFE70 && $cp <= 0xFEFF)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<int>
     */
    private static function utf8ToCps(string $utf8): array
    {
        $cps = [];
        $i = 0;
        $len = strlen($utf8);
        while ($i < $len) {
            $b1 = ord($utf8[$i]);
            if ($b1 < 0x80) {
                $cps[] = $b1;
                $i++;
            } elseif (($b1 & 0xE0) === 0xC0) {
                $cps[] = (($b1 & 0x1F) << 6) | (ord($utf8[$i + 1]) & 0x3F);
                $i += 2;
            } elseif (($b1 & 0xF0) === 0xE0) {
                $cps[] = (($b1 & 0x0F) << 12)
                    | ((ord($utf8[$i + 1]) & 0x3F) << 6)
                    | (ord($utf8[$i + 2]) & 0x3F);
                $i += 3;
            } else {
                $cps[] = (($b1 & 0x07) << 18)
                    | ((ord($utf8[$i + 1]) & 0x3F) << 12)
                    | ((ord($utf8[$i + 2]) & 0x3F) << 6)
                    | (ord($utf8[$i + 3]) & 0x3F);
                $i += 4;
            }
        }

        return $cps;
    }

    /** @param list<int> $cps */
    private static function containsArabic(array $cps): bool
    {
        foreach ($cps as $cp) {
            if (($cp >= 0x0600 && $cp <= 0x06FF)
                || ($cp >= 0x0750 && $cp <= 0x077F)
                || ($cp >= 0x08A0 && $cp <= 0x08FF)
                || ($cp >= 0xFB50 && $cp <= 0xFDFF)
                || ($cp >= 0xFE70 && $cp <= 0xFEFF)) {
                return true;
            }
        }

        return false;
    }

    /**
     * High-level: UTF-8 to list of "shaped" glyph entries after GSUB liga
     * substitution. Each entry = {gid, sourceCps} - sourceCps is the
     * codepoints that this gid represents (1 for regular glyphs,
     * 2+ for ligature glyphs).
     *
     * Side-effect: writes into usedGlyphs with all source codepoints
     * (for building the ToUnicode CMap with correct copy-paste).
     *
     * @return list<array{gid: int, sourceCps: list<int>}>
     */
    public function shapedGlyphs(string $utf8): array
    {
        $decoded = $this->decodeUtf8($utf8);
        $glyphIds = array_map(fn ($e) => $e['gid'], $decoded);
        $codepoints = array_map(fn ($e) => $e['cp'], $decoded);

        $ligatures = $this->ligaturesEnabled ? $this->ttf->ligatures() : null;

        if ($ligatures === null) {
            // No GSUB liga - straight mapping.
            $out = [];
            foreach ($decoded as $i => $entry) {
                $this->usedGlyphs[$entry['gid']] = [$entry['cp']];
                $out[] = ['gid' => $entry['gid'], 'sourceCps' => [$entry['cp']]];
            }

            return $out;
        }

        $result = $ligatures->apply($glyphIds);
        // Mapping: source-glyph-index ranges to result glyph + sources.
        // ligatures->apply() returns {glyphs, sourceMap (ligatureGid -> component-gids)}
        // We need to know which source CODEPOINTS correspond to each
        // ligature gid. This requires matching from LigatureSubstitutions.

        // Simple approach: re-run apply on (cp, gid) pairs sequentially,
        // tracking source-cp positions.
        $out = [];
        $shaped = $result['glyphs'];
        $sourceMap = $result['sourceMap']; // ligatureGid -> list<componentGid>
        $sourceIdx = 0;
        foreach ($shaped as $shapedGid) {
            if (isset($sourceMap[$shapedGid])) {
                $componentCount = count($sourceMap[$shapedGid]);
                $sourceCps = array_slice($codepoints, $sourceIdx, $componentCount);
                $sourceIdx += $componentCount;
            } else {
                $sourceCps = [$codepoints[$sourceIdx]];
                $sourceIdx++;
            }
            $this->usedGlyphs[$shapedGid] = $sourceCps;
            $out[] = ['gid' => $shapedGid, 'sourceCps' => $sourceCps];
        }

        return $out;
    }

    /**
     * Backward-compat: iterate as before. Uses decodeUtf8 without
     * shaping. Side-effect: pollutes usedGlyphs with single-cp entries.
     *
     * Used only for legacy code; new code should use shapedGlyphs().
     *
     * @return iterable<array{cp: int, gid: int}>
     */
    public function utf8ToGlyphs(string $utf8): iterable
    {
        foreach ($this->decodeUtf8($utf8) as $entry) {
            $this->usedGlyphs[$entry['gid']] = [$entry['cp']];
            yield $entry;
        }
    }

    /**
     * Disable ligature substitution. Useful when a font has problematic
     * 'liga' rules or an exact-glyph layout is required.
     */
    public function disableLigatures(): self
    {
        $this->ligaturesEnabled = false;

        return $this;
    }

    /**
     * Kerning adjustment between (left, right) glyphs in PDF text-space units
     * (1000/em), already sign-flipped for ready use in the TJ operator
     * and width-measurement subtraction.
     *
     * Convention:
     *  - Returns POSITIVE for tighter pairs (less space, e.g., AV)
     *  - Returns NEGATIVE for loose pairs (more space - rare)
     *  - 0 if the pair has no kerning
     *
     * For width measurement: total_pdf_units -= kerningPdfUnits(prev, cur).
     * For TJ operator: between runs insert int value = +kerningPdfUnits(prev, cur).
     */
    public function kerningPdfUnits(int $leftGid, int $rightGid): int
    {
        $kt = $this->ttf->kerningTable();
        if ($kt === null) {
            return 0;
        }
        $xAdvanceFu = $kt->lookup($leftGid, $rightGid);
        if ($xAdvanceFu === 0) {
            return 0;
        }
        // GPOS xAdvance negative -> reduce advance -> tighter pair.
        // PDF TJ value positive -> subtract from position -> next glyph left.
        // Convert: PDF_value = -GPOS_value * 1000/em.
        return (int) round(-$xAdvanceFu * 1000 / $this->ttf->unitsPerEm());
    }

    /**
     * Encodes UTF-8 text as a hex glyph-ID string for the simple Tj operator
     * with Identity-H encoding. Applies ligature substitution via
     * shapedGlyphs(). Without kerning - for the kerning-aware version see
     * encodeTextTjArray().
     *
     * Side-effect: usedGlyphs accumulates for the ToUnicode CMap.
     */
    public function encodeText(string $utf8): string
    {
        $hex = '';
        foreach ($this->shapedGlyphs($utf8) as $shaped) {
            $hex .= sprintf('%04X', $shaped['gid']);
        }

        return '<'.$hex.'>';
    }

    /**
     * Kerning-aware encoding for the TJ operator. Returns an array of
     * alternating hex-strings and int adjustments.
     *
     * Example "AVA":
     *   ['<0036>', 74, '<00570036>']
     *   ------  --  ----------
     *   A run   AV  V+A run
     *           kern
     *
     * If the font has no kerning table OR no pairs have kerning, the array
     * contains a single hex-string (in which case the caller can use plain
     * Tj instead of TJ - but it's up to the caller).
     *
     * Side-effect: usedGlyphs accumulates.
     *
     * @return list<string|int>
     */
    public function encodeTextTjArray(string $utf8): array
    {
        $result = [];
        $currentRun = '';
        $prevGid = null;
        foreach ($this->shapedGlyphs($utf8) as $shaped) {
            $gid = $shaped['gid'];
            if ($prevGid !== null) {
                $kern = $this->kerningPdfUnits($prevGid, $gid);
                if ($kern !== 0) {
                    $result[] = '<'.$currentRun.'>';
                    $result[] = $kern;
                    $currentRun = '';
                }
            }
            $currentRun .= sprintf('%04X', $gid);
            $prevGid = $gid;
        }
        if ($currentRun !== '') {
            $result[] = '<'.$currentRun.'>';
        }

        return $result;
    }

    /**
     * Width of a single UTF-8 character in PDF text-space units (1000/em).
     * Useful for measure / layout.
     */
    public function widthOfCharPdfUnits(int $unicodeCodepoint): int
    {
        $gid = $this->ttf->glyphIdForChar($unicodeCodepoint);
        $fontUnits = $this->ttf->advanceWidth($gid);

        return (int) round($fontUnits * 1000 / $this->ttf->unitsPerEm());
    }

    /**
     * Width of a single glyph in PDF text-space units (1000/em).
     * Analog of widthOfCharPdfUnits, but takes an already-resolved glyph ID.
     */
    public function widthOfGlyphPdfUnits(int $gid): int
    {
        $fontUnits = $this->ttf->advanceWidth($gid);

        return (int) round($fontUnits * 1000 / $this->ttf->unitsPerEm());
    }

    /**
     * FontDescriptor - contains metrics and reference to the embedded font file.
     */
    private function buildFontDescriptor(int $fontFileObjId): string
    {
        $upem = $this->ttf->unitsPerEm();
        $toPdf = static fn (int $fu): int => (int) round($fu * 1000 / $upem);

        $bbox = $this->ttf->bbox();
        $bboxStr = sprintf('[%d %d %d %d]',
            $toPdf($bbox[0]),
            $toPdf($bbox[1]),
            $toPdf($bbox[2]),
            $toPdf($bbox[3]),
        );

        // PDF Flags (ISO 32000-1 Table 123). Bit-positions start at 1.
        //   bit 1: FixedPitch
        //   bit 2: Serif
        //   bit 3: Symbolic (any non-Adobe-Latin charset)
        //   bit 4: Script
        //   bit 6: Nonsymbolic
        //   bit 7: Italic
        //   bit 17: AllCap
        //   bit 18: SmallCap
        //   bit 19: ForceBold
        $flags = 0;
        if ($this->ttf->isFixedPitch()) {
            $flags |= 1; // bit 1
        }
        $flags |= 32; // bit 6 - Nonsymbolic (our default; works with Latin + Cyrillic)
        if ($this->ttf->italicAngle() !== 0) {
            $flags |= 64; // bit 7
        }

        return sprintf(
            '<< /Type /FontDescriptor /FontName /%s /Flags %d '
            .'/FontBBox %s /ItalicAngle %d '
            .'/Ascent %d /Descent %d /CapHeight %d /StemV 80 '
            .'/FontFile2 %d 0 R >>',
            $this->ttf->postScriptName(),
            $flags,
            $bboxStr,
            $this->ttf->italicAngle(),
            $toPdf($this->ttf->ascent()),
            $toPdf($this->ttf->descent()),
            $toPdf($this->ttf->ascent()), // CapHeight ~ ascent for POC; refine later
            $fontFileObjId,
        );
    }

    /**
     * CIDFontType2 descendant - contains glyph widths and link to the descriptor.
     *
     * Vertical writing adds /WMode 1 + /W2 array (vertical
     * advance metrics from vmtx).
     */
    private function buildCIDFont(int $descriptorId): string
    {
        $verticalParts = '';
        if ($this->verticalWriting) {
            $verticalParts = '/WMode 1 /W2 '.$this->buildVerticalWidthsArray().' ';
        }

        return sprintf(
            '<< /Type /Font /Subtype /CIDFontType2 /BaseFont /%s '
            .'/CIDSystemInfo << /Registry (Adobe) /Ordering (Identity) /Supplement 0 >> '
            .'/CIDToGIDMap /Identity /FontDescriptor %d 0 R '
            .'/W %s %s>>',
            $this->ttf->postScriptName(),
            $descriptorId,
            $this->buildWidthsArray(),
            $verticalParts,
        );
    }

    /**
     * /W2 array per PDF spec §9.7.4.3 - vertical metrics for CIDFont.
     * Format: [<gid> [<v_y> <v_y_origin> <w1y>] ...]
     * Where:
     *   v_y - vertical displacement to origin point (default 880 thousandths)
     *   v_y_origin - y coordinate relative to horizontal origin
     *   w1y - vertical advance (negative - moves downward).
     *
     * Simplified emission: per glyph derive w1y from vmtx advanceHeight,
     * v_y / v_y_origin use defaults (PDF readers typically tolerate this).
     */
    private function buildVerticalWidthsArray(): string
    {
        if ($this->usedGlyphs === []) {
            return '[]';
        }
        $upem = $this->ttf->unitsPerEm();
        $parts = [];
        ksort($this->usedGlyphs);
        foreach (array_keys($this->usedGlyphs) as $gid) {
            $advHeight = $this->ttf->advanceHeight($gid) ?? 1000;
            // PDF /W2 advance in negative thousandths (1000 unitsPerEm = 1 em).
            $w1y = -(int) round($advHeight * 1000 / $upem);
            // v_y default: 880 (PDF §9.7.4.3); v_y_origin default 500.
            $parts[] = "$gid [880 500 $w1y]";
        }

        return '['.implode(' ', $parts).']';
    }

    /**
     * /W array - widths for each used glyph. Format:
     *   [<gid> [<width1> <width2> ...]  ...]
     *
     * For POC emit individual entries (one glyph per entry).
     * Future work: optimize into ranges.
     */
    private function buildWidthsArray(): string
    {
        if ($this->usedGlyphs === []) {
            return '[]';
        }
        $upem = $this->ttf->unitsPerEm();
        $parts = [];
        // Sort by glyphId for readability and (possibly) future optimization.
        ksort($this->usedGlyphs);
        foreach (array_keys($this->usedGlyphs) as $gid) {
            $pdfWidth = (int) round($this->ttf->advanceWidth($gid) * 1000 / $upem);
            $parts[] = "$gid [$pdfWidth]";
        }

        return '['.implode(' ', $parts).']';
    }

    /**
     * ToUnicode CMap stream - lets a PDF reader convert 2-byte glyph IDs
     * back to Unicode for copy-paste, text-search, and accessibility.
     *
     * Minimal CMap:
     *   /CIDInit /ProcSet findresource begin
     *   12 dict begin
     *   begincmap
     *   /CIDSystemInfo << /Registry (Adobe) /Ordering (UCS) /Supplement 0 >> def
     *   /CMapName /Adobe-Identity-UCS def
     *   /CMapType 2 def
     *   1 begincodespacerange
     *   <0000> <FFFF>
     *   endcodespacerange
     *   N beginbfchar
     *   <GID-hex> <UTF-16-hex>
     *   endbfchar
     *   endcmap
     *   CMapName currentdict /CMap defineresource pop
     *   end end
     */
    private function buildToUnicodeCMap(): string
    {
        $body = "/CIDInit /ProcSet findresource begin\n";
        $body .= "12 dict begin\nbegincmap\n";
        $body .= "/CIDSystemInfo << /Registry (Adobe) /Ordering (UCS) /Supplement 0 >> def\n";
        $body .= "/CMapName /Adobe-Identity-UCS def\n";
        $body .= "/CMapType 2 def\n";
        $body .= "1 begincodespacerange\n<0000> <FFFF>\nendcodespacerange\n";

        $count = count($this->usedGlyphs);
        if ($count > 0) {
            $body .= "$count beginbfchar\n";
            foreach ($this->usedGlyphs as $gid => $cps) {
                // cps - list<int>: single codepoint for regular glyph,
                // multiple for ligature glyph (e.g., fi -> ['f','i']).
                // PDF ToUnicode bfchar: <gid> <utf16-hex> where utf16-hex
                // may be multi-character for ligatures.
                $utf16 = '';
                foreach ($cps as $cp) {
                    $utf16 .= $this->codepointToUtf16BeHex($cp);
                }
                $body .= sprintf("<%04X> <%s>\n", $gid, $utf16);
            }
            $body .= "endbfchar\n";
        }

        $body .= "endcmap\nCMapName currentdict /CMap defineresource pop\nend\nend\n";

        // EOL before `endstream` is a delimiter, not stream data — excluded
        // from /Length (ISO 19005 6.1.7 counts exact bytes).
        return sprintf("<< /Length %d >>\nstream\n%s\nendstream", strlen($body), $body);
    }

    /**
     * Codepoint to UTF-16BE hex for CMap entries. BMP is 4 hex chars.
     * Supplementary plane is 8 hex chars (surrogate pair).
     */
    private function codepointToUtf16BeHex(int $cp): string
    {
        if ($cp <= 0xFFFF) {
            return sprintf('%04X', $cp);
        }
        // Surrogate pair: high 0xD800..0xDBFF, low 0xDC00..0xDFFF.
        $cp -= 0x10000;
        $high = 0xD800 + ($cp >> 10);
        $low = 0xDC00 + ($cp & 0x3FF);

        return sprintf('%04X%04X', $high, $low);
    }
}
