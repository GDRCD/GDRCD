<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * An indirect reference `N G R` (ISO 32000-1 §7.3.10).
 *
 * Resolution to the referenced object is the resolver's job (Phase P2);
 * the parser only records the target coordinates.
 */
final readonly class PdfReference
{
    public function __construct(
        public int $number,
        public int $generation,
    ) {
    }

    /** Stable map key, e.g. "12 0". */
    public function key(): string
    {
        return $this->number . ' ' . $this->generation;
    }
}
