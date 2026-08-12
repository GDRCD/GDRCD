<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Merge;

use Dskripchenko\PhpPdf\Pdf\Document;
use Dskripchenko\PhpPdf\Pdf\PdfFormXObject;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfDictionary;
use Dskripchenko\PhpPdf\Pdf\Reader\ReaderDocument;

/**
 * FPDI-style import: brings a page from an existing PDF into a freshly built
 * {@see Document} as a Form XObject, so new php-pdf content (text, graphics,
 * watermarks) can be drawn over or under the imported page.
 *
 * ```php
 * $doc  = new Document();
 * $page = $doc->addPage();
 * $form = PageImporter::intoDocument($doc, $src, pageIndex: 0);
 * $page->useFormXObject($form, 0, 0, 595, 842);   // place imported page
 * $page->showText('DRAFT', 220, 400, StandardFont::Helvetica, 48);
 * ```
 *
 * The page's rotation and crop-box origin are baked into a leading `cm` inside
 * the form's content and the /BBox is the upright box `[0 0 W H]`, so
 * {@see \Dskripchenko\PhpPdf\Pdf\Page::useFormXObject()} places it correctly
 * for any rotation.
 */
final class PageImporter
{
    /**
     * Import page $pageIndex (0-based) of $src into $doc and return the form.
     */
    public static function intoDocument(Document $doc, ReaderDocument $src, int $pageIndex): PdfFormXObject
    {
        $pages = $src->pages();
        if ($pageIndex < 0 || $pageIndex >= count($pages)) {
            throw new \OutOfRangeException("Page {$pageIndex} does not exist (document has " . count($pages) . ' pages)');
        }
        $page = $pages[$pageIndex];

        $importer = new ObjectImporter();
        $importer->useSource($src);
        $resources = $importer->importValue($page->resources ?? new PdfDictionary([]));

        [$matrix, $w, $h] = PageContent::rotation($page);
        $content = 'q ' . self::formatMatrix($matrix) . " cm\n" . PageContent::bytes($src, $page) . "\nQ";

        $form = new PdfFormXObject($content, 0.0, 0.0, $w, $h);
        $doc->registerImportedForm(
            $form,
            $importer->objects(),
            $resources instanceof PdfDictionary ? $resources : new PdfDictionary([]),
        );

        return $form;
    }

    /**
     * Upright width/height of a source page (after its /Rotate).
     *
     * @return array{float,float}
     */
    public static function pageSize(ReaderDocument $src, int $pageIndex): array
    {
        [, $w, $h] = PageContent::rotation($src->pages()[$pageIndex]);
        return [$w, $h];
    }

    /**
     * @param array{float,float,float,float,float,float} $m
     */
    private static function formatMatrix(array $m): string
    {
        return implode(' ', array_map(static function (float $v): string {
            if ($v === floor($v) && abs($v) < 1e9) {
                return (string) (int) $v;
            }
            return rtrim(rtrim(sprintf('%.4f', $v), '0'), '.');
        }, $m));
    }
}
