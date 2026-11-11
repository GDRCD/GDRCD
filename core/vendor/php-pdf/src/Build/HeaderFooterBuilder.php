<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Build;

use Closure;
use Dskripchenko\PhpPdf\Element\BlockElement;
use Dskripchenko\PhpPdf\Element\HorizontalRule;
use Dskripchenko\PhpPdf\Element\Image;
use Dskripchenko\PhpPdf\Element\Paragraph;
use Dskripchenko\PhpPdf\Element\Run;
use Dskripchenko\PhpPdf\Image\PdfImage;
use Dskripchenko\PhpPdf\Style\Alignment;

/**
 * Builder for header / footer / any top-level block content — a subset of
 * DocumentBuilder without pageSetup and without header/footer/watermark
 * methods (to avoid recursion).
 *
 * Pattern:
 *   ->header(fn(HeaderFooterBuilder $h) => $h
 *       ->paragraph(fn($p) => $p->alignRight()->text('Confidential'))
 *   )
 */
final class HeaderFooterBuilder
{
    /** @var list<BlockElement> */
    private array $blocks = [];

    public static function new(): self
    {
        return new self;
    }

    public function paragraph(string|Closure|Paragraph $content): self
    {
        if ($content instanceof Paragraph) {
            $this->blocks[] = $content;

            return $this;
        }
        if (is_string($content)) {
            $this->blocks[] = new Paragraph([new Run($content)]);

            return $this;
        }
        $p = new ParagraphBuilder;
        $content($p);
        $this->blocks[] = $p->build();

        return $this;
    }

    public function horizontalRule(): self
    {
        $this->blocks[] = new HorizontalRule;

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
            $this->blocks[] = $source;

            return $this;
        }
        $pdfImage = $source instanceof PdfImage ? $source : PdfImage::fromPath($source);
        $this->blocks[] = new Image(
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
        $this->blocks[] = $element;

        return $this;
    }

    /**
     * @return list<BlockElement>
     */
    public function buildBlocks(): array
    {
        return $this->blocks;
    }
}
