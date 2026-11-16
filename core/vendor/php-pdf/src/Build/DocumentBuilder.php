<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Build;

use Closure;
use Dskripchenko\PhpPdf\Document;
use Dskripchenko\PhpPdf\Element\Barcode;
use Dskripchenko\PhpPdf\Element\BarcodeFormat;
use Dskripchenko\PhpPdf\Element\BlockElement;
use Dskripchenko\PhpPdf\Element\HorizontalRule;
use Dskripchenko\PhpPdf\Element\Image;
use Dskripchenko\PhpPdf\Element\ListNode;
use Dskripchenko\PhpPdf\Element\PageBreak;
use Dskripchenko\PhpPdf\Element\Paragraph;
use Dskripchenko\PhpPdf\Element\Run;
use Dskripchenko\PhpPdf\Element\Table;
use Dskripchenko\PhpPdf\Style\ListFormat;
use Dskripchenko\PhpPdf\Image\PdfImage;
use Dskripchenko\PhpPdf\Layout\Engine;
use Dskripchenko\PhpPdf\Section;
use Dskripchenko\PhpPdf\Style\Alignment;
use Dskripchenko\PhpPdf\Style\PageSetup;
use Dskripchenko\PhpPdf\Style\RunStyle;

/**
 * Fluent builder for Document.
 *
 * Mirrors php-docx DocumentBuilder for API symmetry — a unified API
 * between Word and PDF generation allows switching the backend in one line.
 *
 * Pattern:
 *   DocumentBuilder::new()
 *       ->pageSetup(new PageSetup(...))
 *       ->heading(1, 'Invoice')
 *       ->paragraph('Customer: Acme Co.')
 *       ->paragraph(fn($p) => $p->text('Mix ')->bold('here'))
 *       ->pageBreak()
 *       ->toBytes();
 */
final class DocumentBuilder
{
    /** @var list<BlockElement> */
    private array $body = [];

    /** @var list<BlockElement> */
    private array $headerBlocks = [];

    /** @var list<BlockElement> */
    private array $footerBlocks = [];

    private ?string $watermarkText = null;

    private ?PdfImage $watermarkImage = null;

    private ?float $watermarkImageWidthPt = null;

    private ?float $watermarkImageOpacity = null;

    private ?float $watermarkTextOpacity = null;

    /** @var list<BlockElement>|null */
    private ?array $firstPageHeaderBlocks = null;

    /** @var list<BlockElement>|null */
    private ?array $firstPageFooterBlocks = null;

    private PageSetup $pageSetup;

    public function __construct()
    {
        $this->pageSetup = new PageSetup;
    }

    public static function new(): self
    {
        return new self;
    }

    // ── Page setup ────────────────────────────────────────────────

    public function pageSetup(PageSetup $setup): self
    {
        $this->pageSetup = $setup;

        return $this;
    }

    public function header(Closure $build): self
    {
        $b = new HeaderFooterBuilder;
        $build($b);
        $this->headerBlocks = $b->buildBlocks();

        return $this;
    }

    public function footer(Closure $build): self
    {
        $b = new HeaderFooterBuilder;
        $build($b);
        $this->footerBlocks = $b->buildBlocks();

        return $this;
    }

    /**
     * Watermark — diagonal text on every page (under body content).
     * Pass an empty string or null to disable.
     */
    public function watermark(?string $text): self
    {
        $this->watermarkText = $text;

        return $this;
    }

    /**
     * Image watermark — drawn at the center of every page.
     * $widthPt null → 50% page width; aspect ratio is preserved.
     * Pass null to disable.
     */
    public function watermarkImage(?PdfImage $image, ?float $widthPt = null): self
    {
        $this->watermarkImage = $image;
        $this->watermarkImageWidthPt = $widthPt;

        return $this;
    }

    /**
     * Opacity for image watermark. 0..1 (1=opaque, 0=invisible).
     * null = full opacity (default).
     */
    public function watermarkImageOpacity(?float $opacity): self
    {
        $this->watermarkImageOpacity = $opacity;

        return $this;
    }

    public function watermarkTextOpacity(?float $opacity): self
    {
        $this->watermarkTextOpacity = $opacity;

        return $this;
    }

    /**
     * First-page header — overrides the regular header on page 1.
     * Pass an empty Closure for an empty header on the first page.
     */
    public function firstPageHeader(Closure $build): self
    {
        $b = new HeaderFooterBuilder;
        $build($b);
        $this->firstPageHeaderBlocks = $b->buildBlocks();

        return $this;
    }

    public function firstPageFooter(Closure $build): self
    {
        $b = new HeaderFooterBuilder;
        $build($b);
        $this->firstPageFooterBlocks = $b->buildBlocks();

        return $this;
    }

    /**
     * Convenience — disable header/footer on the first page (cover).
     */
    public function noHeaderFooterOnFirstPage(): self
    {
        $this->firstPageHeaderBlocks = [];
        $this->firstPageFooterBlocks = [];

        return $this;
    }

    // ── Block content ─────────────────────────────────────────────

    /**
     * Adds a paragraph. $content can be:
     *  - string — plain single-run text
     *  - Closure(ParagraphBuilder) — for complex inline content/style
     *  - Paragraph — a ready AST node
     */
    public function paragraph(string|Closure|Paragraph $content): self
    {
        if ($content instanceof Paragraph) {
            $this->body[] = $content;

            return $this;
        }
        if (is_string($content)) {
            $this->body[] = new Paragraph([new Run($content)]);

            return $this;
        }

        $b = new ParagraphBuilder;
        $content($b);
        $this->body[] = $b->build();

        return $this;
    }

    /**
     * Heading paragraph of level 1..6. $content — string or Closure(ParagraphBuilder).
     */
    public function heading(int $level, string|Closure $content): self
    {
        if ($level < 1 || $level > 6) {
            throw new \InvalidArgumentException("Heading level must be 1..6, got $level.");
        }

        $b = new ParagraphBuilder;
        $b->heading($level);
        if (is_string($content)) {
            $b->text($content);
        } else {
            $content($b);
        }
        $this->body[] = $b->build();

        return $this;
    }

    public function pageBreak(): self
    {
        $this->body[] = new PageBreak;

        return $this;
    }

    public function horizontalRule(): self
    {
        $this->body[] = new HorizontalRule;

        return $this;
    }

    /**
     * Empty line — empty paragraph (useful for visual spacing).
     */
    public function emptyLine(): self
    {
        $this->body[] = new Paragraph;

        return $this;
    }

    /**
     * Directly adds a BlockElement to the AST. Escape hatch for types
     * not covered by fluent methods.
     */
    public function block(BlockElement $element): self
    {
        $this->body[] = $element;

        return $this;
    }

    /**
     * Block-level image. $source — file path (string), a ready PdfImage,
     * or an AST Image. Sizing/alignment via keyword arguments.
     *
     * Examples:
     *   ->image('/path/to/logo.png')
     *   ->image('/path/to/photo.jpg', widthPt: 200, alignment: Alignment::Center)
     *   ->image($pdfImage, widthPt: 300, heightPt: 200)
     */
    /**
     * Bullet list. $build — Closure(ListBuilder) or a ready ListNode.
     */
    public function bulletList(Closure|ListNode $build): self
    {
        return $this->listOfFormat(ListFormat::Bullet, $build);
    }

    /**
     * Ordered list. $format — Decimal/LowerLetter/UpperLetter/LowerRoman/
     * UpperRoman.
     */
    public function orderedList(Closure|ListNode $build, ListFormat $format = ListFormat::Decimal): self
    {
        return $this->listOfFormat($format, $build);
    }

    private function listOfFormat(ListFormat $format, Closure|ListNode $build): self
    {
        if ($build instanceof ListNode) {
            $this->body[] = $build;

            return $this;
        }
        $b = new ListBuilder($format);
        $build($b);
        $this->body[] = $b->build();

        return $this;
    }

    /**
     * Block-level table. $content — Closure(TableBuilder) or a ready Table.
     */
    public function table(Closure|Table $content): self
    {
        if ($content instanceof Table) {
            $this->body[] = $content;

            return $this;
        }
        $b = new TableBuilder;
        $content($b);
        $this->body[] = $b->build();

        return $this;
    }

    /**
     * Add a Code 128 barcode (or another supported format).
     *
     * \$widthPt null → auto: 1pt × moduleCount. A typical SKU barcode
     * (~10 chars) at 1pt-per-module = 150-200pt wide.
     */
    public function barcode(
        string $value,
        BarcodeFormat $format = BarcodeFormat::Code128,
        ?float $widthPt = null,
        float $heightPt = 40.0,
        bool $showText = true,
        float $textSizePt = 8.0,
        Alignment $alignment = Alignment::Start,
        float $spaceBeforePt = 0,
        float $spaceAfterPt = 0,
    ): self {
        $this->body[] = new Barcode(
            value: $value,
            format: $format,
            widthPt: $widthPt,
            heightPt: $heightPt,
            showText: $showText,
            textSizePt: $textSizePt,
            alignment: $alignment,
            spaceBeforePt: $spaceBeforePt,
            spaceAfterPt: $spaceAfterPt,
        );

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
            $this->body[] = $source;

            return $this;
        }
        $pdfImage = $source instanceof PdfImage ? $source : PdfImage::fromPath($source);
        $this->body[] = new Image(
            source: $pdfImage,
            widthPt: $widthPt,
            heightPt: $heightPt,
            alignment: $alignment,
            spaceBeforePt: $spaceBeforePt,
            spaceAfterPt: $spaceAfterPt,
        );

        return $this;
    }

    // ── Convenience aliases ───────────────────────────────────────

    public function text(string $text, ?RunStyle $style = null): self
    {
        return $this->paragraph(fn(ParagraphBuilder $p) => $p->text($text, $style));
    }

    // ── Finalizers ────────────────────────────────────────────────

    public function build(): Document
    {
        return new Document(new Section(
            body: $this->body,
            pageSetup: $this->pageSetup,
            headerBlocks: $this->headerBlocks,
            footerBlocks: $this->footerBlocks,
            watermarkText: $this->watermarkText,
            firstPageHeaderBlocks: $this->firstPageHeaderBlocks,
            firstPageFooterBlocks: $this->firstPageFooterBlocks,
            watermarkImage: $this->watermarkImage,
            watermarkImageWidthPt: $this->watermarkImageWidthPt,
            watermarkImageOpacity: $this->watermarkImageOpacity,
            watermarkTextOpacity: $this->watermarkTextOpacity,
        ));
    }

    public function toBytes(?Engine $engine = null): string
    {
        return $this->build()->toBytes($engine);
    }

    public function toFile(string $path, ?Engine $engine = null): int
    {
        return $this->build()->toFile($path, $engine);
    }
}
