<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf;

use Dskripchenko\PhpPdf\Element\BlockElement;
use Dskripchenko\PhpPdf\Image\PdfImage;
use Dskripchenko\PhpPdf\Style\PageSetup;

/**
 * Section — body content plus per-section page setup, header, footer,
 * and optional watermark.
 *
 * A Document holds one primary Section plus any number of additional
 * sections. Each additional section starts on a new page and may use a
 * different paper size, orientation, margins, or chrome.
 */
final readonly class Section
{
    /**
     * @param  list<BlockElement>  $body
     * @param  list<BlockElement>  $headerBlocks
     * @param  list<BlockElement>  $footerBlocks
     * @param  list<BlockElement>|null  $firstPageHeaderBlocks  Null = use
     *         `$headerBlocks` for page 1. Empty list = blank header on page 1
     *         (cover page convention).
     * @param  list<BlockElement>|null  $firstPageFooterBlocks  Null = use
     *         `$footerBlocks` for page 1. Empty list = blank footer on page 1.
     */
    public function __construct(
        public array $body = [],
        public PageSetup $pageSetup = new PageSetup,
        public array $headerBlocks = [],
        public array $footerBlocks = [],
        public ?string $watermarkText = null,
        public ?array $firstPageHeaderBlocks = null,
        public ?array $firstPageFooterBlocks = null,
        /**
         * Image watermark (drawn first, behind text watermark if both set).
         */
        public ?PdfImage $watermarkImage = null,
        public ?float $watermarkImageWidthPt = null,
        /**
         * Opacity for image watermark via PDF ExtGState /ca. Range (0, 1)
         * applies alpha; null or ≥1 means full opacity.
         */
        public ?float $watermarkImageOpacity = null,
        public ?float $watermarkTextOpacity = null,
        /**
         * Footnote placement mode:
         *  - null: endnote-style — all footnotes rendered at the end of the
         *    section body (default).
         *  - float > 0: reserve this many points at each page bottom for a
         *    footnote zone; footnotes are flushed per-page below body content.
         *
         * Allow roughly 12-15 pt per expected footnote line. E.g. five short
         * footnotes per page → ~75 pt.
         */
        public ?float $footnoteBottomReservedPt = null,
    ) {}

    public function hasHeader(): bool
    {
        return $this->headerBlocks !== [];
    }

    public function hasFooter(): bool
    {
        return $this->footerBlocks !== [];
    }

    public function hasWatermark(): bool
    {
        return $this->hasTextWatermark() || $this->hasImageWatermark();
    }

    public function hasTextWatermark(): bool
    {
        return $this->watermarkText !== null && $this->watermarkText !== '';
    }

    public function hasImageWatermark(): bool
    {
        return $this->watermarkImage !== null;
    }

    /**
     * @return list<BlockElement>
     */
    public function effectiveHeaderBlocksFor(int $pageNumber): array
    {
        if ($pageNumber === 1 && $this->firstPageHeaderBlocks !== null) {
            return $this->firstPageHeaderBlocks;
        }

        return $this->headerBlocks;
    }

    /**
     * @return list<BlockElement>
     */
    public function effectiveFooterBlocksFor(int $pageNumber): array
    {
        if ($pageNumber === 1 && $this->firstPageFooterBlocks !== null) {
            return $this->firstPageFooterBlocks;
        }

        return $this->footerBlocks;
    }
}
