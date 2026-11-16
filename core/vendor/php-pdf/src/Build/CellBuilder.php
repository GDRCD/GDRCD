<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Build;

use Closure;
use Dskripchenko\PhpPdf\Element\BlockElement;
use Dskripchenko\PhpPdf\Element\Cell;
use Dskripchenko\PhpPdf\Element\Image;
use Dskripchenko\PhpPdf\Element\Paragraph;
use Dskripchenko\PhpPdf\Element\Run;
use Dskripchenko\PhpPdf\Image\PdfImage;
use Dskripchenko\PhpPdf\Style\Alignment;
use Dskripchenko\PhpPdf\Style\BorderSet;
use Dskripchenko\PhpPdf\Style\CellStyle;
use Dskripchenko\PhpPdf\Style\VerticalAlignment;

/**
 * Fluent builder for a table Cell. A Cell contains arbitrary
 * BlockElements — Paragraph/Image/nested Table. CellBuilder proxies
 * inline content to ParagraphBuilder when quick text needs to be added.
 */
final class CellBuilder
{
    /** @var list<BlockElement> */
    private array $children = [];

    private CellStyle $style;

    private int $columnSpan = 1;

    private int $rowSpan = 1;

    public function __construct()
    {
        $this->style = new CellStyle;
    }

    public static function new(): self
    {
        return new self;
    }

    // ── Content ────────────────────────────────────────────────────

    /**
     * Plain text as a single-run Paragraph.
     */
    public function text(string $text): self
    {
        $this->children[] = new Paragraph([new Run($text)]);

        return $this;
    }

    /**
     * Paragraph. $content — string | Closure(ParagraphBuilder) | Paragraph.
     */
    public function paragraph(string|Closure|Paragraph $content): self
    {
        if ($content instanceof Paragraph) {
            $this->children[] = $content;

            return $this;
        }
        if (is_string($content)) {
            $this->children[] = new Paragraph([new Run($content)]);

            return $this;
        }
        $p = new ParagraphBuilder;
        $content($p);
        $this->children[] = $p->build();

        return $this;
    }

    public function image(
        string|PdfImage|Image $source,
        ?float $widthPt = null,
        ?float $heightPt = null,
        Alignment $alignment = Alignment::Start,
        float $spaceBeforePt = 0,
        float $spaceAfterPt = 0,
    ): self {
        if ($source instanceof Image) {
            $this->children[] = $source;

            return $this;
        }
        $pdfImage = $source instanceof PdfImage ? $source : PdfImage::fromPath($source);
        $this->children[] = new Image(
            source: $pdfImage,
            widthPt: $widthPt,
            heightPt: $heightPt,
            alignment: $alignment,
            spaceBeforePt: $spaceBeforePt,
            spaceAfterPt: $spaceAfterPt,
        );

        return $this;
    }

    public function block(BlockElement $element): self
    {
        $this->children[] = $element;

        return $this;
    }

    // ── Cell-level style ───────────────────────────────────────────

    public function background(string $hex): self
    {
        $this->style = $this->style->withBackgroundColor($hex);

        return $this;
    }

    public function borders(BorderSet $borders): self
    {
        $this->style = $this->style->withBorders($borders);

        return $this;
    }

    public function padding(float $pt): self
    {
        $this->style = $this->style->withPadding($pt);

        return $this;
    }

    public function paddingSides(?float $top = null, ?float $right = null, ?float $bottom = null, ?float $left = null): self
    {
        $this->style = $this->style->copy(
            paddingTopPt: $top,
            paddingRightPt: $right,
            paddingBottomPt: $bottom,
            paddingLeftPt: $left,
        );

        return $this;
    }

    public function vAlign(VerticalAlignment $v): self
    {
        $this->style = $this->style->withVerticalAlign($v);

        return $this;
    }

    public function vAlignMiddle(): self
    {
        return $this->vAlign(VerticalAlignment::Center);
    }

    public function vAlignBottom(): self
    {
        return $this->vAlign(VerticalAlignment::Bottom);
    }

    public function width(float $pt): self
    {
        $this->style = $this->style->withWidthPt($pt);

        return $this;
    }

    public function setStyle(CellStyle $style): self
    {
        $this->style = $style;

        return $this;
    }

    public function span(int $columnSpan, int $rowSpan = 1): self
    {
        $this->columnSpan = $columnSpan;
        $this->rowSpan = $rowSpan;

        return $this;
    }

    public function build(): Cell
    {
        return new Cell(
            children: $this->children,
            style: $this->style,
            columnSpan: $this->columnSpan,
            rowSpan: $this->rowSpan,
        );
    }
}
