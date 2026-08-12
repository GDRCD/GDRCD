<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Image\PdfImage;
use Dskripchenko\PhpPdf\Style\Alignment;

/**
 * Raster image (PNG / JPEG). Lives both as a top-level block and as an
 * inline atom inside paragraphs:
 *  - At Section.body level → block mode (full-line with alignment).
 *  - Inside Paragraph.children → inline mode (atom in text flow with
 *    baseline-aligned bottom; line-height grows to fit image height).
 *
 * Sizing rules:
 *  - Both widthPt and heightPt set: used as-is.
 *  - Only one set: the other derived from native aspect ratio.
 *  - Both null: native dimensions at 72 DPI (1 px = 1 pt).
 *
 * `$altText` populates the /Alt entry on the surrounding struct element
 * in Tagged PDF / PDF/UA mode.
 */
final readonly class Image implements BlockElement, InlineElement
{
    public function __construct(
        public PdfImage $source,
        public ?float $widthPt = null,
        public ?float $heightPt = null,
        public Alignment $alignment = Alignment::Start,
        public float $spaceBeforePt = 0,
        public float $spaceAfterPt = 0,
        public ?string $altText = null,
    ) {}

    public static function fromPath(
        string $path,
        ?float $widthPt = null,
        ?float $heightPt = null,
        Alignment $alignment = Alignment::Start,
        float $spaceBeforePt = 0,
        float $spaceAfterPt = 0,
        ?string $altText = null,
    ): self {
        return new self(
            source: PdfImage::fromPath($path),
            widthPt: $widthPt,
            heightPt: $heightPt,
            alignment: $alignment,
            spaceBeforePt: $spaceBeforePt,
            spaceAfterPt: $spaceAfterPt,
            altText: $altText,
        );
    }

    public static function fromBytes(
        string $bytes,
        ?float $widthPt = null,
        ?float $heightPt = null,
        Alignment $alignment = Alignment::Start,
        float $spaceBeforePt = 0,
        float $spaceAfterPt = 0,
        ?string $altText = null,
    ): self {
        return new self(
            source: PdfImage::fromBytes($bytes),
            widthPt: $widthPt,
            heightPt: $heightPt,
            alignment: $alignment,
            spaceBeforePt: $spaceBeforePt,
            spaceAfterPt: $spaceAfterPt,
            altText: $altText,
        );
    }

    /**
     * Effective rendered dimensions in points, applying defaulting and
     * aspect ratio preservation.
     *
     * @return array{0: float, 1: float} [widthPt, heightPt]
     */
    public function effectiveSizePt(): array
    {
        $nativeW = (float) $this->source->widthPx;
        $nativeH = (float) $this->source->heightPx;

        if ($this->widthPt !== null && $this->heightPt !== null) {
            return [$this->widthPt, $this->heightPt];
        }
        if ($this->widthPt !== null) {
            $ratio = $nativeH / max($nativeW, 1);

            return [$this->widthPt, $this->widthPt * $ratio];
        }
        if ($this->heightPt !== null) {
            $ratio = $nativeW / max($nativeH, 1);

            return [$this->heightPt * $ratio, $this->heightPt];
        }

        return [$nativeW, $nativeH];
    }
}
