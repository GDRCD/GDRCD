<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * The 14 standard PDF fonts (Adobe base-14) — guaranteed to be available
 * in every PDF reader without embedding.
 *
 * Limitations:
 *  - WinAnsi encoding (Latin-1 + some extended Latin); no Cyrillic,
 *    Greek, CJK, etc.
 *  - Symbol uses a custom encoding for Greek/math glyphs.
 *  - ZapfDingbats uses a custom encoding for shapes/arrows.
 *
 * Use cases:
 *  - Minimal bundle size (~600 bytes on disk vs ~411KB for embedded
 *    Liberation).
 *  - Latin-1-only text (English, Western European languages).
 *
 * For Cyrillic / full Unicode use an embedded TTF — see PdfFont (subset,
 * ToUnicode CMap, kerning, ligatures).
 *
 * Reference: ISO 32000-1 §9.6.2.2.
 */
enum StandardFont: string
{
    case Helvetica = 'Helvetica';
    case HelveticaBold = 'Helvetica-Bold';
    case HelveticaOblique = 'Helvetica-Oblique';
    case HelveticaBoldOblique = 'Helvetica-BoldOblique';

    case TimesRoman = 'Times-Roman';
    case TimesBold = 'Times-Bold';
    case TimesItalic = 'Times-Italic';
    case TimesBoldItalic = 'Times-BoldItalic';

    case Courier = 'Courier';
    case CourierBold = 'Courier-Bold';
    case CourierOblique = 'Courier-Oblique';
    case CourierBoldOblique = 'Courier-BoldOblique';

    case Symbol = 'Symbol';
    case ZapfDingbats = 'ZapfDingbats';

    /**
     * PostScript name for /BaseFont in the font dictionary — equal to
     * the enum value.
     */
    public function postScriptName(): string
    {
        return $this->value;
    }

    /**
     * PDF font dictionary emission for a Type1 standard font:
     *   << /Type /Font /Subtype /Type1 /BaseFont /<name> /Encoding /WinAnsiEncoding >>
     *
     * Symbol and ZapfDingbats have a built-in encoding (not /WinAnsiEncoding),
     * so /Encoding is omitted for them.
     */
    public function pdfDictionary(): string
    {
        $needsEncoding = ! in_array($this, [self::Symbol, self::ZapfDingbats], true);
        $encoding = $needsEncoding ? ' /Encoding /WinAnsiEncoding' : '';

        return sprintf(
            '<< /Type /Font /Subtype /Type1 /BaseFont /%s%s >>',
            $this->value,
            $encoding,
        );
    }

    /**
     * Heuristic: which stylesheet group this font belongs to.
     */
    public function isSerif(): bool
    {
        return in_array($this, [
            self::TimesRoman, self::TimesBold, self::TimesItalic, self::TimesBoldItalic,
        ], true);
    }

    public function isMonospaced(): bool
    {
        return in_array($this, [
            self::Courier, self::CourierBold, self::CourierOblique, self::CourierBoldOblique,
        ], true);
    }

    public function isBold(): bool
    {
        return str_contains($this->value, 'Bold');
    }

    public function isItalic(): bool
    {
        return str_contains($this->value, 'Italic') || str_contains($this->value, 'Oblique');
    }
}
