<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Style;

/**
 * Table-level style — width, alignment, default cell padding/borders,
 * spacing.
 *
 * `widthPercent` (0..100) is relative to the surrounding content area;
 * if both `widthPt` and `widthPercent` are null the table spans the full
 * content width. `defaultCellBorder` and `defaultCellStyle` provide fall-
 * back styling for cells without their own overrides.
 *
 * `borderCollapse: true` makes adjacent cells share borders (first-drawn
 * wins for color/style). Otherwise `borderSpacingPt` controls the gap
 * between cells in CSS `separate` border-spacing mode.
 */
final readonly class TableStyle
{
    public function __construct(
        public ?float $widthPt = null,
        public ?float $widthPercent = null,
        public Alignment $alignment = Alignment::Start,
        public ?BorderSet $borders = null,
        public ?Border $defaultCellBorder = null,
        public CellStyle $defaultCellStyle = new CellStyle,
        public float $spaceBeforePt = 0,
        public float $spaceAfterPt = 6,
        public bool $borderCollapse = false,
        public float $borderSpacingPt = 0,
    ) {}

    public function copy(
        ?float $widthPt = null,
        ?float $widthPercent = null,
        ?Alignment $alignment = null,
        ?BorderSet $borders = null,
        ?Border $defaultCellBorder = null,
        ?CellStyle $defaultCellStyle = null,
        ?float $spaceBeforePt = null,
        ?float $spaceAfterPt = null,
        ?bool $borderCollapse = null,
        ?float $borderSpacingPt = null,
    ): self {
        return new self(
            widthPt: $widthPt ?? $this->widthPt,
            widthPercent: $widthPercent ?? $this->widthPercent,
            alignment: $alignment ?? $this->alignment,
            borders: $borders ?? $this->borders,
            defaultCellBorder: $defaultCellBorder ?? $this->defaultCellBorder,
            defaultCellStyle: $defaultCellStyle ?? $this->defaultCellStyle,
            spaceBeforePt: $spaceBeforePt ?? $this->spaceBeforePt,
            spaceAfterPt: $spaceAfterPt ?? $this->spaceAfterPt,
            borderCollapse: $borderCollapse ?? $this->borderCollapse,
            borderSpacingPt: $borderSpacingPt ?? $this->borderSpacingPt,
        );
    }
}
