<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Layout;

use Dskripchenko\PhpPdf\Pdf\Document as PdfDocument;
use Dskripchenko\PhpPdf\Pdf\Page;
use Dskripchenko\PhpPdf\Style\PageSetup;

/**
 * Mutable state that Engine mutates while walking the AST.
 *
 * Contains:
 *  - currentPage — where rendering currently targets
 *  - cursorY — "top" of the next line (in PDF coords; grows upward)
 *  - leftX/contentWidth — content area bounds
 *  - topY/bottomY — vertical bounds (for overflow detection)
 *  - pdf — root Pdf\Document (needed for addPage on overflow)
 *
 * Engine is the sole writer; LayoutContext exposes these fields publicly
 * because they are frequently read/updated during the layout pass.
 */
final class LayoutContext
{
    public function __construct(
        public PdfDocument $pdf,
        public Page $currentPage,
        public float $cursorY,
        public float $leftX,
        public float $contentWidth,
        public float $bottomY,
        public float $topY,
        public PageSetup $pageSetup,
        // Multi-column state. columnCount == 1 → single-column
        // (no column flow). columnCount > 1 → forcePageBreak
        // overflow → next column; last column → real page break.
        public int $columnCount = 1,
        public int $currentColumn = 0,
        public float $columnGapPt = 0,
        public float $columnOriginLeftX = 0,
        public float $columnOriginContentWidth = 0,
        // Collected footnotes/endnotes per section (rendered at
        // end of section as endnotes block).
        /** @var list<string> */
        public array $footnotes = [],
        // Suppress paragraph BDC/EMC wrapping (used when a heading
        // sets its own H1-H6 tagging around paragraph render).
        public bool $skipParagraphTag = false,
        // Re-entrance guard for header/footer rendering. forcePageBreak
        // invokes renderHeaderFooter; if inside header rendering a block
        // does not fit and tries forcePageBreak — infinite loop. This flag
        // suppresses header/footer on the new page if we are already in the
        // header/footer render path; also suppresses forcePageBreak itself
        // (overflow truncates).
        public bool $inHeaderFooterRender = false,
        /**
         * Per-page footnote bottom reservation (in points). null =
         * endnotes mode (all footnotes at section's end).
         * > 0 = footnote zone reserved at each page bottom, footnotes for
         * current page rendered before page break.
         */
        public ?float $footnoteReserveBottomPt = null,
        /**
         * Footnote count in $footnotes[] before the start of the current page.
         * Footnotes added since this index = "current page's footnotes",
         * rendered at page bottom on page break.
         */
        public int $pageFootnoteStart = 0,
    ) {}
}
