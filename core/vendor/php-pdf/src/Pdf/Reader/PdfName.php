<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * A PDF name object (ISO 32000-1 §7.3.5), e.g. `/Type`.
 *
 * The stored value is the decoded name *without* the leading slash and with
 * `#XX` escapes already resolved.
 */
final readonly class PdfName
{
    public function __construct(public string $value)
    {
    }
}
