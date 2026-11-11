<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Merge;

use Dskripchenko\PhpPdf\Pdf\Reader\PdfDictionary;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfNull;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfReference;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfValueSerializer;

/**
 * Serializes a flat object set (from {@see ObjectImporter}) into a complete PDF
 * file with a classic cross-reference table.
 *
 * The output is unencrypted; imported stream bodies keep their original
 * `/Filter` chain, and `/Length` is restated from the actual byte count so
 * decrypted (AES-shortened) streams stay valid.
 */
final class MergeSerializer
{
    private const HEADER = "%PDF-1.7\n%\xE2\xE3\xCF\xD3\n";

    /**
     * @param array<int,mixed> $objects newId → value (contiguous IDs from 1)
     * @param int              $rootId  object ID of the document catalog
     * @param array<string,mixed> $trailerExtra extra trailer entries (e.g. /Info)
     */
    public function serialize(array $objects, int $rootId, array $trailerExtra = []): string
    {
        // References already carry their final output numbers here (the
        // importer assigned them), so the ref map is the identity.
        $encoder = new PdfValueSerializer(static fn (int $n): int => $n);

        $out = self::HEADER;
        $count = count($objects);
        $offsets = [];

        for ($id = 1; $id <= $count; $id++) {
            $offsets[$id] = strlen($out);
            $out .= $id . " 0 obj\n";
            $out .= $encoder->encode($objects[$id] ?? PdfNull::instance());
            $out .= "\nendobj\n";
        }

        $xrefOffset = strlen($out);
        $size = $count + 1;

        $out .= "xref\n";
        $out .= "0 {$size}\n";
        $out .= "0000000000 65535 f \n";
        for ($id = 1; $id <= $count; $id++) {
            $out .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }

        $out .= "trailer\n";
        $out .= $encoder->encode(new PdfDictionary(array_merge([
            'Size' => $size,
            'Root' => new PdfReference($rootId, 0),
        ], $trailerExtra)));
        $out .= "\nstartxref\n{$xrefOffset}\n%%EOF\n";

        return $out;
    }
}
