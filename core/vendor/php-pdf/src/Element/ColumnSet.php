<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

/**
 * Multi-column layout block.
 *
 * Body blocks flow column-first: content fills column 0 down to the
 * bottom, continues in column 1, and so on. When the last column is
 * full the layout breaks to a new page and continues at column 0.
 *
 * ColumnSet cannot be nested — inner content must be regular blocks
 * (Paragraph, Table, ListNode, ...). After the ColumnSet ends, single-
 * column flow resumes from the lowest used Y across all columns.
 */
final readonly class ColumnSet implements BlockElement
{
    /**
     * @param  list<BlockElement>  $body
     * @param  int  $columnCount   Number of columns (2-6 typical).
     * @param  float  $columnGapPt Horizontal gap between columns.
     */
    public function __construct(
        public array $body,
        public int $columnCount = 2,
        public float $columnGapPt = 12.0,
        public float $spaceBeforePt = 0,
        public float $spaceAfterPt = 0,
    ) {
        if ($columnCount < 1) {
            throw new \InvalidArgumentException('ColumnSet columnCount must be >= 1');
        }
    }
}
