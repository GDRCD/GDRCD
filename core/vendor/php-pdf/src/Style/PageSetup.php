<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Style;

/**
 * Full page setup — paper size, orientation, margins, and optional
 * custom dimensions for non-standard formats (business cards, banners).
 *
 * Used per Section to define page geometry. `firstPageNumber` shifts the
 * value emitted by `Field::page()` — useful when a document is chapter
 * N of a larger manual and should start numbering at the offset.
 */
final readonly class PageSetup
{
    public function __construct(
        public PaperSize $paperSize = PaperSize::A4,
        public Orientation $orientation = Orientation::Portrait,
        public PageMargins $margins = new PageMargins,
        /**
         * Override paper size with explicit [widthPt, heightPt]. Useful
         * for non-standard formats (business cards, banners).
         *
         * @var array{0: float, 1: float}|null
         */
        public ?array $customDimensionsPt = null,
        public int $firstPageNumber = 1,
    ) {}

    /**
     * @return array{0: float, 1: float}  [widthPt, heightPt]
     */
    public function dimensions(): array
    {
        if ($this->customDimensionsPt !== null) {
            return $this->orientation === Orientation::Portrait
                ? $this->customDimensionsPt
                : [$this->customDimensionsPt[1], $this->customDimensionsPt[0]];
        }

        return $this->orientation->applyTo($this->paperSize);
    }

    /**
     * Default content width. Mirrored margins and gutter are applied
     * per page via `contentWidthPtForPage()`.
     */
    public function contentWidthPt(): float
    {
        return $this->dimensions()[0] - $this->margins->leftPt - $this->margins->rightPt - $this->margins->gutterPt;
    }

    public function contentHeightPt(): float
    {
        return $this->dimensions()[1] - $this->margins->topPt - $this->margins->bottomPt;
    }

    /**
     * Effective left X for a given 1-based page number (honors mirrored
     * margins and gutter).
     */
    public function leftXForPage(int $pageNumber): float
    {
        [$left, $_] = $this->margins->effectiveLeftRightFor($pageNumber);

        return $left;
    }

    /**
     * Effective content width for a given 1-based page number.
     */
    public function contentWidthPtForPage(int $pageNumber): float
    {
        [$left, $right] = $this->margins->effectiveLeftRightFor($pageNumber);

        return $this->dimensions()[0] - $left - $right;
    }
}
