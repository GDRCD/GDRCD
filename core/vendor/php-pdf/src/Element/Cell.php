<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\CellStyle;

/**
 * Table cell holding arbitrary block content (paragraphs, images, nested
 * tables). Cell itself is not a BlockElement — it only lives inside Row.
 *
 * `columnSpan` and `rowSpan` (both ≥ 1) let a cell stretch across
 * multiple columns or rows; the layout engine resolves the geometry.
 */
final readonly class Cell
{
    /**
     * @param  list<BlockElement>  $children
     */
    public function __construct(
        public array $children = [],
        public CellStyle $style = new CellStyle,
        public int $columnSpan = 1,
        public int $rowSpan = 1,
    ) {
        if ($columnSpan < 1) {
            throw new \InvalidArgumentException("columnSpan must be ≥ 1, got $columnSpan");
        }
        if ($rowSpan < 1) {
            throw new \InvalidArgumentException("rowSpan must be ≥ 1, got $rowSpan");
        }
    }

    public function isEmpty(): bool
    {
        return $this->children === [];
    }
}
