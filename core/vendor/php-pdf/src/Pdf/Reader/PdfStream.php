<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * A PDF stream object (ISO 32000-1 §7.3.8): a dictionary followed by raw bytes.
 *
 * `$raw` is the *undecoded* stream body exactly as it appears in the file. Any
 * `/Filter` chain (FlateDecode, LZW, …) is applied later by the filter layer
 * (Phase P3); this value object stays transport-only.
 */
final readonly class PdfStream
{
    public function __construct(
        public PdfDictionary $dict,
        public string $raw,
    ) {
    }
}
