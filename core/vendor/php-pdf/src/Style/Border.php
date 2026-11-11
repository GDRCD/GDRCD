<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Style;

/**
 * Single-edge border (paragraph, cell, or table).
 *
 * Size is stored in eighths of a point (OOXML convention); default 4 = 0.5pt.
 * Color is RGB hex without `#`, lowercase (e.g. `'14b8a6'`).
 */
final readonly class Border
{
    public function __construct(
        public BorderStyle $style = BorderStyle::Single,
        public int $sizeEighthsOfPoint = 4,
        public string $color = '000000',
    ) {}

    /**
     * Border width in points (PDF stroke width unit).
     */
    public function widthPt(): float
    {
        return $this->sizeEighthsOfPoint / 8.0;
    }
}
