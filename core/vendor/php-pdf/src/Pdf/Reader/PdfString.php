<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * A PDF string object (ISO 32000-1 §7.3.4) — literal `( ... )` or hex `< ... >`.
 *
 * `$bytes` holds the raw decoded byte string (escapes and hex already
 * resolved). Text interpretation (PDFDocEncoding / UTF-16BE) is a higher-level
 * concern and not applied here.
 */
final readonly class PdfString
{
    public function __construct(
        public string $bytes,
        /** True when the source syntax was a hex string `< ... >`. */
        public bool $hex = false,
    ) {
    }
}
