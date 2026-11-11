<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\TableStyle;

/**
 * Block-level table — list of rows containing cells.
 *
 * `columnWidthsPt` is an explicit list of column widths in points; null
 * distributes width evenly. Total column count is derived from the max
 * sum of `columnSpan` values across rows.
 */
final readonly class Table implements BlockElement
{
    /**
     * @param  list<Row>  $rows
     * @param  list<float>|null  $columnWidthsPt
     */
    public function __construct(
        public array $rows = [],
        public TableStyle $style = new TableStyle,
        public ?array $columnWidthsPt = null,
        public ?string $caption = null,
    ) {}

    public function columnCount(): int
    {
        $max = 0;
        foreach ($this->rows as $row) {
            $sum = 0;
            foreach ($row->cells as $cell) {
                $sum += $cell->columnSpan;
            }
            if ($sum > $max) {
                $max = $sum;
            }
        }

        return $max;
    }

    public function isEmpty(): bool
    {
        return $this->rows === [];
    }
}
