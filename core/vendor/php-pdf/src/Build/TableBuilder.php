<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Build;

use Closure;
use Dskripchenko\PhpPdf\Element\Row;
use Dskripchenko\PhpPdf\Element\Table;
use Dskripchenko\PhpPdf\Style\Alignment;
use Dskripchenko\PhpPdf\Style\Border;
use Dskripchenko\PhpPdf\Style\BorderSet;
use Dskripchenko\PhpPdf\Style\CellStyle;
use Dskripchenko\PhpPdf\Style\TableStyle;

/**
 * Fluent builder for Table.
 *
 * Pattern:
 *   TableBuilder::new()
 *       ->widthPercent(90)
 *       ->alignCenter()
 *       ->defaultCellBorder(new Border(BorderStyle::Single))
 *       ->columnWidths([60, 200, 80])
 *       ->row(fn(RowBuilder $r) => $r->header()->cells(['SKU', 'Name', 'Qty']))
 *       ->row(fn(RowBuilder $r) => $r
 *           ->cell('A-100')
 *           ->cell(fn(CellBuilder $c) => $c->bold('Widget')->background('eef'))
 *           ->cell('42')
 *       )
 *       ->build();
 */
final class TableBuilder
{
    /** @var list<Row> */
    private array $rows = [];

    /** @var list<float>|null */
    private ?array $columnWidthsPt = null;

    private TableStyle $style;

    private ?string $caption = null;

    public function __construct()
    {
        $this->style = new TableStyle;
    }

    public static function new(): self
    {
        return new self;
    }

    // ── Sizing & layout ────────────────────────────────────────────

    public function width(float $pt): self
    {
        $this->style = $this->style->copy(widthPt: $pt);

        return $this;
    }

    public function widthPercent(float $percent): self
    {
        $this->style = $this->style->copy(widthPercent: $percent);

        return $this;
    }

    /**
     * @param  list<float>  $widthsPt
     */
    public function columnWidths(array $widthsPt): self
    {
        $this->columnWidthsPt = $widthsPt;

        return $this;
    }

    public function align(Alignment $alignment): self
    {
        $this->style = $this->style->copy(alignment: $alignment);

        return $this;
    }

    public function alignCenter(): self
    {
        return $this->align(Alignment::Center);
    }

    public function alignRight(): self
    {
        return $this->align(Alignment::End);
    }

    // ── Borders ────────────────────────────────────────────────────

    public function borders(BorderSet $borders): self
    {
        $this->style = $this->style->copy(borders: $borders);

        return $this;
    }

    public function defaultCellBorder(Border $border): self
    {
        $this->style = $this->style->copy(defaultCellBorder: $border);

        return $this;
    }

    public function defaultCellStyle(CellStyle $style): self
    {
        $this->style = $this->style->copy(defaultCellStyle: $style);

        return $this;
    }

    // ── Misc style ─────────────────────────────────────────────────

    public function caption(string $text): self
    {
        $this->caption = $text;

        return $this;
    }

    public function spaceBefore(float $pt): self
    {
        $this->style = $this->style->copy(spaceBeforePt: $pt);

        return $this;
    }

    public function spaceAfter(float $pt): self
    {
        $this->style = $this->style->copy(spaceAfterPt: $pt);

        return $this;
    }

    public function setStyle(TableStyle $style): self
    {
        $this->style = $style;

        return $this;
    }

    // ── Rows ───────────────────────────────────────────────────────

    public function row(Closure|Row $content): self
    {
        if ($content instanceof Row) {
            $this->rows[] = $content;

            return $this;
        }
        $b = new RowBuilder;
        $content($b);
        $this->rows[] = $b->build();

        return $this;
    }

    public function headerRow(Closure $content): self
    {
        $b = new RowBuilder;
        $b->header();
        $content($b);
        $this->rows[] = $b->build();

        return $this;
    }

    public function build(): Table
    {
        return new Table(
            rows: $this->rows,
            style: $this->style,
            columnWidthsPt: $this->columnWidthsPt,
            caption: $this->caption,
        );
    }
}
