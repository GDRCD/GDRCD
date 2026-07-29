<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Merge;

use Dskripchenko\PhpPdf\Pdf\Reader\PdfDictionary;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfName;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfStream;
use Dskripchenko\PhpPdf\Pdf\Reader\ReaderDocument;
use Dskripchenko\PhpPdf\Pdf\Reader\ReaderPage;

/**
 * Imports a source page as a reusable Form XObject (ISO 32000-1 §8.10): the
 * page's content streams are decoded and concatenated into the form body, its
 * resource closure is imported, `/BBox` is the crop box, and `/Matrix` bakes in
 * the page's `/Rotate` so the embedded page appears upright.
 */
final class PageXObjectBuilder
{
    public function __construct(private readonly ObjectImporter $importer)
    {
    }

    /**
     * @return array{\Dskripchenko\PhpPdf\Pdf\Reader\PdfReference, float, float}
     *         [reference, upright-width, upright-height]
     */
    public function build(ReaderDocument $doc, ReaderPage $page): array
    {
        $this->importer->useSource($doc);

        $body = PageContent::bytes($doc, $page);
        [$matrix, $w, $h] = PageContent::rotation($page);
        $resources = $this->importer->importValue($page->resources ?? new PdfDictionary([]));

        $dict = new PdfDictionary([
            'Type' => new PdfName('XObject'),
            'Subtype' => new PdfName('Form'),
            'FormType' => 1,
            'BBox' => array_map('floatval', $page->cropBox),
            'Matrix' => $matrix,
            'Resources' => $resources,
            'Filter' => new PdfName('FlateDecode'),
        ]);

        $ref = new \Dskripchenko\PhpPdf\Pdf\Reader\PdfReference(
            $this->importer->allocate(new PdfStream($dict, (string) gzcompress($body))),
            0,
        );

        return [$ref, $w, $h];
    }
}
