<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Layout;

use Dskripchenko\PhpPdf\Pdf\PdfFont;

/**
 * Measures visual width of UTF-8 strings in pt for a given font and size.
 *
 * With kerning enabled, applies GPOS pair-adjustments when the font has a
 * kerning table. Without kerning (PDF base-14 or older fonts without GPOS),
 * degrades to a simple sum of glyph-advance widths.
 *
 * Not covered here:
 *  - GSUB ligature substitutions (fi/fl/ffi handled elsewhere)
 *  - Hyphenation
 *  - Complex script shaping (Arabic, Indic)
 *
 * Acceptable error for line-breaking purposes is < 2% without kerning;
 * with kerning it is ~0% for PDF base-14 and < 0.5% for embedded TTFs.
 */
final class TextMeasurer
{
    public function __construct(
        private readonly PdfFont $font,
        private readonly float $sizePt,
        private readonly bool $useKerning = true,
    ) {}

    public function widthPt(string $utf8): float
    {
        $totalUnits = 0;
        $prevGid = null;
        foreach ($this->font->utf8ToGlyphs($utf8) as ['gid' => $gid]) {
            $totalUnits += $this->font->widthOfGlyphPdfUnits($gid);
            if ($this->useKerning && $prevGid !== null) {
                // kerningPdfUnits > 0 for tighter pairs (less width).
                $totalUnits -= $this->font->kerningPdfUnits($prevGid, $gid);
            }
            $prevGid = $gid;
        }

        return $totalUnits * $this->sizePt / 1000;
    }

    public function widthOfCodepointPt(int $cp): float
    {
        return $this->font->widthOfCharPdfUnits($cp) * $this->sizePt / 1000;
    }

    public function sizePt(): float
    {
        return $this->sizePt;
    }
}
