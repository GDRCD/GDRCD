<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Merge;

use Dskripchenko\PhpPdf\Pdf\Reader\PdfStream;
use Dskripchenko\PhpPdf\Pdf\Reader\ReaderDocument;
use Dskripchenko\PhpPdf\Pdf\Reader\ReaderPage;

/**
 * Shared extraction of a source page's content stream and its rotation matrix,
 * used by both embed paths ({@see PageXObjectBuilder} and {@see PageImporter}).
 */
final class PageContent
{
    /** Decode and concatenate all of a page's content streams. */
    public static function bytes(ReaderDocument $doc, ReaderPage $page): string
    {
        $contents = $doc->deref($page->dict->get('Contents'));
        $streams = is_array($contents) ? $contents : [$contents];

        $parts = [];
        foreach ($streams as $entry) {
            $stream = $doc->deref($entry);
            if ($stream instanceof PdfStream) {
                $parts[] = $doc->streamData($stream);
            }
        }
        return implode("\n", $parts);
    }

    /**
     * Matrix mapping the crop box to the upright box [0,0,W,H] for the page's
     * `/Rotate`, plus the upright width and height.
     *
     * @return array{array{float,float,float,float,float,float}, float, float}
     */
    public static function rotation(ReaderPage $page): array
    {
        [$cx0, $cy0, $cx1, $cy1] = $page->cropBox;
        $w = $cx1 - $cx0;
        $h = $cy1 - $cy0;

        return match ($page->rotate) {
            90 => [[0.0, -1.0, 1.0, 0.0, -$cy0, $cx1], $h, $w],
            180 => [[-1.0, 0.0, 0.0, -1.0, $cx1, $cy1], $w, $h],
            270 => [[0.0, 1.0, -1.0, 0.0, $cy1, -$cx0], $h, $w],
            default => [[1.0, 0.0, 0.0, 1.0, -$cx0, -$cy0], $w, $h],
        };
    }
}
