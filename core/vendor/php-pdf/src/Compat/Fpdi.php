<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Compat;

use Dskripchenko\PhpPdf\Pdf\Document as PdfDocument;
use Dskripchenko\PhpPdf\Pdf\Merge\PageImporter;
use Dskripchenko\PhpPdf\Pdf\Page;
use Dskripchenko\PhpPdf\Pdf\PdfFormXObject;
use Dskripchenko\PhpPdf\Pdf\Reader\ReaderDocument;

/**
 * Drop-in style facade for the most common `\setasign\Fpdi\Fpdi` usage —
 * importing pages from an existing PDF and placing them on new pages:
 *
 *     // before                              // after
 *     $pdf = new \setasign\Fpdi\Fpdi();     $pdf = new \Dskripchenko\PhpPdf\Compat\Fpdi();
 *     $n = $pdf->setSourceFile('in.pdf');   $n = $pdf->setSourceFile('in.pdf');
 *     $tpl = $pdf->importPage(1);           $tpl = $pdf->importPage(1);
 *     $pdf->AddPage();                      $pdf->AddPage();
 *     $pdf->useTemplate($tpl);              $pdf->useTemplate($tpl);
 *     $pdf->Output('F', 'out.pdf');         $pdf->Output('F', 'out.pdf');
 *
 * Coordinates follow FPDF conventions: origin at the TOP-left of the page,
 * y grows downward, values in the constructor's unit (mm by default).
 *
 * Covered: setSourceFile (file or bytes via setSourceBytes), importPage,
 * getImportedPageSize/getTemplateSize, AddPage, useTemplate (position,
 * proportional width/height), Output (F/S/D/I). Encrypted sources: pass
 * the password to setSourceFile.
 *
 * Deliberately NOT covered: FPDF drawing methods (Cell, MultiCell,
 * SetFont...) — draw on the returned {@see page()} with the native
 * `Pdf\Page` API, or use {@see \Dskripchenko\PhpPdf\Pdf\Merge\PdfMerger}
 * for plain concatenation, which also carries annotations and outlines
 * (this facade, like FPDI, imports page content only).
 *
 * See docs/en/MIGRATION-FROM-FPDI.md for the full mapping table.
 */
final class Fpdi
{
    private readonly PdfDocument $doc;

    private ?Page $page = null;

    private ?ReaderDocument $source = null;

    /** @var list<array{form: PdfFormXObject, widthPt: float, heightPt: float}> */
    private array $templates = [];

    /** Points per user unit. */
    private readonly float $k;

    /** @var array{0: float, 1: float} Default page size in points [w, h]. */
    private readonly array $pageSizePt;

    /**
     * @param  string  $orientation  'P' or 'L' (FPDF-style)
     * @param  string  $unit  'pt', 'mm', 'cm', or 'in'
     * @param  string|array{0: float, 1: float}  $size  Format name
     *         ('A3'..'A5', 'Letter', 'Legal') or [width, height] in $unit.
     */
    public function __construct(string $orientation = 'P', string $unit = 'mm', string|array $size = 'A4')
    {
        $this->k = match (strtolower($unit)) {
            'pt' => 1.0,
            'mm' => 72 / 25.4,
            'cm' => 72 / 2.54,
            'in' => 72.0,
            default => throw new \InvalidArgumentException("Unknown unit: $unit"),
        };

        $sizes = [
            'a3' => [841.89, 1190.55],
            'a4' => [595.28, 841.89],
            'a5' => [419.53, 595.28],
            'letter' => [612.0, 792.0],
            'legal' => [612.0, 1008.0],
        ];
        if (is_array($size)) {
            $dims = [$size[0] * $this->k, $size[1] * $this->k];
        } else {
            $dims = $sizes[strtolower($size)]
                ?? throw new \InvalidArgumentException("Unknown page size: $size");
        }
        if (strtoupper($orientation) === 'L') {
            $dims = [max($dims), min($dims)];
        }
        $this->pageSizePt = [$dims[0], $dims[1]];

        $this->doc = PdfDocument::new();
    }

    /**
     * Open a source PDF. Returns its page count, like FPDI.
     */
    public function setSourceFile(string $path, string $password = ''): int
    {
        return $this->setSourceBytes((string) file_get_contents($path), $password);
    }

    public function setSourceBytes(string $bytes, string $password = ''): int
    {
        $this->source = ReaderDocument::fromBytes($bytes, $password);

        return count($this->source->pages());
    }

    /**
     * Import a page (1-based, like FPDI) from the current source.
     * Returns a template handle for useTemplate().
     */
    public function importPage(int $pageNumber): int
    {
        if ($this->source === null) {
            throw new \LogicException('Call setSourceFile() before importPage().');
        }
        $form = PageImporter::intoDocument($this->doc, $this->source, $pageNumber - 1);
        $this->templates[] = [
            'form' => $form,
            'widthPt' => $form->bboxWidth(),
            'heightPt' => $form->bboxHeight(),
        ];

        return count($this->templates) - 1;
    }

    /**
     * Template size in user units: ['width' => ..., 'height' => ...].
     * Pass $width or $height to get the proportional counterpart.
     *
     * @return array{width: float, height: float}
     */
    public function getImportedPageSize(int $tpl, ?float $width = null, ?float $height = null): array
    {
        $t = $this->templates[$tpl] ?? throw new \OutOfRangeException("Unknown template #$tpl");
        $ratio = $t['widthPt'] / $t['heightPt'];
        if ($width !== null) {
            return ['width' => $width, 'height' => $width / $ratio];
        }
        if ($height !== null) {
            return ['width' => $height * $ratio, 'height' => $height];
        }

        return ['width' => $t['widthPt'] / $this->k, 'height' => $t['heightPt'] / $this->k];
    }

    /**
     * FPDI alias for getImportedPageSize().
     *
     * @return array{width: float, height: float}
     */
    public function getTemplateSize(int $tpl, ?float $width = null, ?float $height = null): array
    {
        return $this->getImportedPageSize($tpl, $width, $height);
    }

    /**
     * Start a new page. $size (in user units) overrides the constructor
     * default — pass the imported page's size to preserve its geometry.
     *
     * @param  array{0: float, 1: float}|array{width: float, height: float}|null  $size
     */
    public function AddPage(string $orientation = '', ?array $size = null): Page
    {
        if ($size !== null) {
            $w = ($size['width'] ?? $size[0]) * $this->k;
            $h = ($size['height'] ?? $size[1]) * $this->k;
        } else {
            [$w, $h] = $this->pageSizePt;
        }
        if (strtoupper($orientation) === 'L' && $h > $w) {
            [$w, $h] = [$h, $w];
        }

        return $this->page = $this->doc->addPage(customDimensionsPt: [$w, $h]);
    }

    /**
     * Place an imported page on the current page. FPDF coordinates:
     * $x/$y from the top-left corner, in user units. With only $width or
     * only $height the other side scales proportionally; with neither the
     * template keeps its original size.
     *
     * @return array{width: float, height: float}  Placed size in user units.
     */
    public function useTemplate(
        int $tpl,
        float $x = 0,
        float $y = 0,
        ?float $width = null,
        ?float $height = null,
    ): array {
        if ($this->page === null) {
            throw new \LogicException('Call AddPage() before useTemplate().');
        }
        $t = $this->templates[$tpl] ?? throw new \OutOfRangeException("Unknown template #$tpl");

        $size = $this->getImportedPageSize($tpl, $width, $height);
        $wPt = $size['width'] * $this->k;
        $hPt = $size['height'] * $this->k;

        // Top-left origin, y down (FPDF) → PDF bottom-left origin, y up.
        $xPt = $x * $this->k;
        $yPt = $this->page->heightPt() - $y * $this->k - $hPt;
        $this->page->useFormXObject($t['form'], $xPt, $yPt, $wPt, $hPt);

        return $size;
    }

    /**
     * The current page, for drawing over the imported content with the
     * native `Pdf\Page` API (showText, images, fields...).
     */
    public function page(): ?Page
    {
        return $this->page;
    }

    /**
     * The underlying document, for native-API features (encryption,
     * signing, metadata...).
     */
    public function document(): PdfDocument
    {
        return $this->doc;
    }

    /**
     * FPDF-compatible output. Accepts both modern Output($dest, $name)
     * and legacy Output($name, $dest) argument orders, like FPDF itself.
     */
    public function Output(string $dest = '', string $name = ''): ?string
    {
        // Legacy order: Output('file.pdf', 'F').
        if (! in_array(strtoupper($dest), ['', 'I', 'D', 'F', 'S'], true)) {
            [$dest, $name] = [$name, $dest];
        }

        $bytes = $this->doc->toBytes();

        switch (strtoupper($dest !== '' ? $dest : ($name !== '' ? 'F' : 'I'))) {
            case 'S':
                return $bytes;
            case 'F':
                if ($name === '') {
                    throw new \InvalidArgumentException("Output('F') requires a file name.");
                }
                file_put_contents($name, $bytes);

                return null;
            case 'D':
            case 'I':
            default:
                if (! headers_sent()) {
                    header('Content-Type: application/pdf');
                    header(sprintf(
                        'Content-Disposition: %s; filename="%s"',
                        strtoupper($dest) === 'D' ? 'attachment' : 'inline',
                        addslashes($name !== '' ? $name : 'document.pdf'),
                    ));
                    header('Content-Length: '.strlen($bytes));
                }
                echo $bytes;

                return null;
        }
    }
}
