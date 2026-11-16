<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Style;

/**
 * Block-level paragraph style — alignment, spacing, indentation,
 * borders, padding, background.
 *
 * All sizes are in PDF points (1pt = 1/72 inch). All fields have
 * sensible defaults (left-aligned, no indent, no spacing, no borders).
 */
final readonly class ParagraphStyle
{
    public function __construct(
        public Alignment $alignment = Alignment::Start,
        public float $spaceBeforePt = 0,
        public float $spaceAfterPt = 0,
        public float $indentLeftPt = 0,
        public float $indentRightPt = 0,
        public float $indentFirstLinePt = 0,
        /** Line height as a multiplier of font size. Null = font default (~1.2). */
        public ?float $lineHeightMult = null,
        /** Absolute line height in pt. Overrides `lineHeightMult` when set. */
        public ?float $lineHeightPt = null,
        public bool $pageBreakBefore = false,
        public ?BorderSet $borders = null,
        public float $paddingTopPt = 0,
        public float $paddingRightPt = 0,
        public float $paddingBottomPt = 0,
        public float $paddingLeftPt = 0,
        public ?string $backgroundColor = null,
    ) {}

    public function isEmpty(): bool
    {
        return $this->alignment === Alignment::Start
            && $this->spaceBeforePt === 0.0
            && $this->spaceAfterPt === 0.0
            && $this->indentLeftPt === 0.0
            && $this->indentRightPt === 0.0
            && $this->indentFirstLinePt === 0.0
            && $this->lineHeightMult === null
            && ! $this->pageBreakBefore
            && $this->borders === null;
    }

    /**
     * Return a copy with overridden fields.
     */
    public function copy(
        ?Alignment $alignment = null,
        ?float $spaceBeforePt = null,
        ?float $spaceAfterPt = null,
        ?float $indentLeftPt = null,
        ?float $indentRightPt = null,
        ?float $indentFirstLinePt = null,
        ?float $lineHeightMult = null,
        ?bool $pageBreakBefore = null,
        ?BorderSet $borders = null,
        ?float $paddingTopPt = null,
        ?float $paddingRightPt = null,
        ?float $paddingBottomPt = null,
        ?float $paddingLeftPt = null,
        ?string $backgroundColor = null,
    ): self {
        return new self(
            alignment: $alignment ?? $this->alignment,
            spaceBeforePt: $spaceBeforePt ?? $this->spaceBeforePt,
            spaceAfterPt: $spaceAfterPt ?? $this->spaceAfterPt,
            indentLeftPt: $indentLeftPt ?? $this->indentLeftPt,
            indentRightPt: $indentRightPt ?? $this->indentRightPt,
            indentFirstLinePt: $indentFirstLinePt ?? $this->indentFirstLinePt,
            lineHeightMult: $lineHeightMult ?? $this->lineHeightMult,
            pageBreakBefore: $pageBreakBefore ?? $this->pageBreakBefore,
            borders: $borders ?? $this->borders,
            paddingTopPt: $paddingTopPt ?? $this->paddingTopPt,
            paddingRightPt: $paddingRightPt ?? $this->paddingRightPt,
            paddingBottomPt: $paddingBottomPt ?? $this->paddingBottomPt,
            paddingLeftPt: $paddingLeftPt ?? $this->paddingLeftPt,
            backgroundColor: $backgroundColor ?? $this->backgroundColor,
        );
    }
}
