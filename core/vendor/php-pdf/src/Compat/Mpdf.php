<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Compat;

use Dskripchenko\PhpPdf\Document;
use Dskripchenko\PhpPdf\Element\PageBreak;
use Dskripchenko\PhpPdf\Layout\Engine;
use Dskripchenko\PhpPdf\Section;
use Dskripchenko\PhpPdf\Style\Orientation;
use Dskripchenko\PhpPdf\Style\PageMargins;
use Dskripchenko\PhpPdf\Style\PageSetup;
use Dskripchenko\PhpPdf\Style\PaperSize;

/**
 * Drop-in style facade for the most common `\Mpdf\Mpdf` usage:
 *
 *     // before                       // after
 *     $mpdf = new \Mpdf\Mpdf();      $mpdf = new \Dskripchenko\PhpPdf\Compat\Mpdf();
 *     $mpdf->WriteHTML($html);       $mpdf->WriteHTML($html);
 *     $mpdf->Output('f.pdf', 'F');   $mpdf->Output('f.pdf', 'F');
 *
 * Covered: WriteHTML (repeated calls append), AddPage, Output with the
 * four mpdf destinations (F file / S string / D download / I inline),
 * SetTitle/SetAuthor/SetCreator/SetSubject/SetKeywords, and the config
 * keys `format`, `orientation`, `margin_left/right/top/bottom` (in mm,
 * like mpdf).
 *
 * Deliberately NOT covered (use the native API instead): mpdf-specific
 * HTML extensions (<pagebreak>, <barcode>, ...), WriteHTML modes
 * (HEADER_CSS etc. — the mode argument is ignored, full HTML is assumed),
 * SetHeader/SetFooter shortcodes, and font configuration arrays. For
 * non-Latin text pass an {@see Engine} with embedded TTF fonts — the
 * default engine uses the base-14 fonts (WinAnsi) only.
 *
 * See docs/en/MIGRATION-FROM-MPDF.md for the full mapping table.
 */
final class Mpdf
{
    /** @var list<string> HTML accumulated per page-break-separated chunk. */
    private array $chunks = [''];

    /** @var array<string, string> */
    private array $metadata = [];

    private readonly PageSetup $pageSetup;

    private const MM_TO_PT = 72 / 25.4;

    /**
     * @param  array<string, mixed>  $config  mpdf-style config. Honoured
     *         keys: format, orientation, margin_left, margin_right,
     *         margin_top, margin_bottom. Unknown keys are ignored.
     */
    public function __construct(
        array $config = [],
        private readonly ?Engine $engine = null,
    ) {
        $format = $config['format'] ?? 'A4';
        $orientationFromFormat = null;
        if (is_string($format) && preg_match('/^(.*)-([LP])$/', $format, $m) === 1) {
            // mpdf allows "A4-L" style formats.
            $format = $m[1];
            $orientationFromFormat = $m[2];
        }
        $paper = is_string($format)
            ? (self::paperSizes()[strtolower($format)] ?? PaperSize::A4)
            : PaperSize::A4;

        $orientation = $config['orientation'] ?? $orientationFromFormat ?? 'P';

        $defaults = new PageMargins;
        $this->pageSetup = new PageSetup(
            paperSize: $paper,
            orientation: strtoupper((string) $orientation) === 'L'
                ? Orientation::Landscape
                : Orientation::Portrait,
            margins: new PageMargins(
                topPt: self::mm($config['margin_top'] ?? null) ?? $defaults->topPt,
                rightPt: self::mm($config['margin_right'] ?? null) ?? $defaults->rightPt,
                bottomPt: self::mm($config['margin_bottom'] ?? null) ?? $defaults->bottomPt,
                leftPt: self::mm($config['margin_left'] ?? null) ?? $defaults->leftPt,
            ),
        );
    }

    /**
     * Append an HTML fragment. Repeated calls concatenate, as in mpdf.
     * The mpdf `$mode` argument is accepted but ignored — pass complete
     * HTML (the overwhelmingly common usage).
     */
    public function WriteHTML(string $html, int $mode = 0): void
    {
        $this->chunks[count($this->chunks) - 1] .= $html;
    }

    /**
     * Force a page break before subsequently written HTML.
     */
    public function AddPage(): void
    {
        $this->chunks[] = '';
    }

    public function SetTitle(string $title): void
    {
        $this->metadata['Title'] = $title;
    }

    public function SetAuthor(string $author): void
    {
        $this->metadata['Author'] = $author;
    }

    public function SetCreator(string $creator): void
    {
        $this->metadata['Creator'] = $creator;
    }

    public function SetSubject(string $subject): void
    {
        $this->metadata['Subject'] = $subject;
    }

    public function SetKeywords(string $keywords): void
    {
        $this->metadata['Keywords'] = $keywords;
    }

    /**
     * mpdf-compatible output. $dest: '' / 'I' inline, 'D' download,
     * 'F' file (to $name), 'S' return bytes as string.
     */
    public function Output(string $name = '', string $dest = ''): ?string
    {
        $bytes = $this->build();

        switch (strtoupper($dest !== '' ? $dest : ($name !== '' ? 'F' : 'I'))) {
            case 'S':
                return $bytes;
            case 'F':
                if ($name === '') {
                    throw new \InvalidArgumentException("Output(..., 'F') requires a file name.");
                }
                file_put_contents($name, $bytes);

                return null;
            case 'D':
                $this->emitHeaders($name !== '' ? $name : 'document.pdf', 'attachment', strlen($bytes));
                echo $bytes;

                return null;
            case 'I':
            default:
                $this->emitHeaders($name !== '' ? $name : 'document.pdf', 'inline', strlen($bytes));
                echo $bytes;

                return null;
        }
    }

    /**
     * The assembled document, for callers who want to keep going with the
     * native API (fonts, encryption, signing, PDF/A...).
     */
    public function toDocument(): Document
    {
        $blocks = [];
        foreach ($this->chunks as $i => $html) {
            if ($i > 0) {
                $blocks[] = new PageBreak;
            }
            $parsed = Document::fromHtml($html);
            foreach ($parsed->section->body as $block) {
                $blocks[] = $block;
            }
        }

        return new Document(
            new Section($blocks, pageSetup: $this->pageSetup),
            metadata: $this->metadata,
        );
    }

    private function build(): string
    {
        return $this->toDocument()->toBytes($this->engine);
    }

    private function emitHeaders(string $filename, string $disposition, int $length): void
    {
        if (headers_sent()) {
            return;
        }
        header('Content-Type: application/pdf');
        header(sprintf('Content-Disposition: %s; filename="%s"', $disposition, addslashes($filename)));
        header('Content-Length: '.$length);
        header('Cache-Control: private, max-age=0, must-revalidate');
    }

    /**
     * @return array<string, PaperSize>
     */
    private static function paperSizes(): array
    {
        return [
            'a3' => PaperSize::A3,
            'a4' => PaperSize::A4,
            'a5' => PaperSize::A5,
            'a6' => PaperSize::A6,
            'letter' => PaperSize::Letter,
            'legal' => PaperSize::Legal,
            'tabloid' => PaperSize::Tabloid,
            'executive' => PaperSize::Executive,
        ];
    }

    private static function mm(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value * self::MM_TO_PT : null;
    }
}
