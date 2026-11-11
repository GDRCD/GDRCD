<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * PDF/X print conformance configuration (ISO 15930).
 *
 * Variants:
 *  - PDF/X-1a:2003 — CMYK + spot colors only (not enforced; markers only)
 *  - PDF/X-3:2003 — allows RGB + CMYK + Lab + spot; ICC profile mandatory.
 *    Most flexible variant compatible with our RGB rendering.
 *  - PDF/X-4 — adds transparency and layers (PDF 1.6+), ICC mandatory.
 *
 * Emission applies:
 *  - /Type /OutputIntent /S /GTS_PDFX with embedded ICC profile
 *  - /Trapped key in /Info dictionary
 *  - /Metadata stream with pdfx: XMP namespace markers
 *
 * Not enforced (caller must comply manually):
 *  - All fonts embedded (default behavior — already true)
 *  - No transparency for X-1a / X-3
 *  - No JavaScript / external launches
 *  - No encryption (mutually exclusive)
 *
 * Conformance markers alone do not guarantee ISO 15930 compliance; use
 * a PDF/X validator (Acrobat Preflight, callas pdfToolbox) for production
 * verification.
 */
final readonly class PdfXConfig
{
    public const VARIANT_X1A = 'PDF/X-1a:2003';

    public const VARIANT_X3 = 'PDF/X-3:2003';

    public const VARIANT_X4 = 'PDF/X-4';

    public const TRAPPED_TRUE = 'True';

    public const TRAPPED_FALSE = 'False';

    public const TRAPPED_UNKNOWN = 'Unknown';

    public function __construct(
        public string $iccProfilePath,
        public string $iccProfileName = 'sRGB IEC61966-2.1',
        /** Output condition identifier (e.g. ICC reference name "FOGRA39"). */
        public string $outputConditionIdentifier = 'sRGB IEC61966-2.1',
        public string $outputCondition = '',
        public string $registryName = 'http://www.color.org',
        public string $variant = self::VARIANT_X3,
        public string $trapped = self::TRAPPED_FALSE,
        public string $title = '',
        public string $author = '',
    ) {
        if (! is_readable($iccProfilePath)) {
            throw new \InvalidArgumentException("ICC profile not readable: $iccProfilePath");
        }
        if (! in_array($variant, [self::VARIANT_X1A, self::VARIANT_X3, self::VARIANT_X4], true)) {
            throw new \InvalidArgumentException("Unknown PDF/X variant: $variant");
        }
        if (! in_array($trapped, [self::TRAPPED_TRUE, self::TRAPPED_FALSE, self::TRAPPED_UNKNOWN], true)) {
            throw new \InvalidArgumentException("Trapped value must be True/False/Unknown");
        }
    }

    public function iccProfileBytes(): string
    {
        return (string) file_get_contents($this->iccProfilePath);
    }

    /**
     * Render XMP metadata stream with pdfx: namespace markers.
     */
    public function xmpMetadata(string $producer = 'dskripchenko/php-pdf'): string
    {
        $created = (new \DateTimeImmutable)->format('Y-m-d\TH:i:sP');
        $title = self::xmlEscape($this->title);
        $author = self::xmlEscape($this->author);
        $producer = self::xmlEscape($producer);
        $variant = self::xmlEscape($this->variant);

        return <<<XMP
<?xpacket begin="\u{FEFF}" id="W5M0MpCehiHzreSzNTczkc9d"?>
<x:xmpmeta xmlns:x="adobe:ns:meta/">
  <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
    <rdf:Description rdf:about=""
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:pdf="http://ns.adobe.com/pdf/1.3/"
        xmlns:xmp="http://ns.adobe.com/xap/1.0/"
        xmlns:pdfx="http://ns.adobe.com/pdfx/1.3/"
        xmlns:pdfxid="http://www.npes.org/pdfx/ns/id/">
      <dc:title><rdf:Alt><rdf:li xml:lang="x-default">$title</rdf:li></rdf:Alt></dc:title>
      <dc:creator><rdf:Seq><rdf:li>$author</rdf:li></rdf:Seq></dc:creator>
      <pdf:Producer>$producer</pdf:Producer>
      <xmp:CreateDate>$created</xmp:CreateDate>
      <xmp:ModifyDate>$created</xmp:ModifyDate>
      <pdfx:GTS_PDFXVersion>$variant</pdfx:GTS_PDFXVersion>
      <pdfxid:GTS_PDFXVersion>$variant</pdfxid:GTS_PDFXVersion>
    </rdf:Description>
  </rdf:RDF>
</x:xmpmeta>
<?xpacket end="w"?>
XMP;
    }

    private static function xmlEscape(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
