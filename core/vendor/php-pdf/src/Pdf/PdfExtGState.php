<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * PDF Extended Graphics State (ExtGState).
 *
 * Describes graphics state parameters that cannot be expressed via inline
 * operators (for example opacity / transparency). Applied in a content
 * stream via the `<name> gs` operator.
 *
 * ISO 32000-1 §8.4.5 / §11.3 — Transparent imaging model.
 * Transparency requires PDF 1.4+. php-pdf headers as 1.7 → OK.
 *
 * Fields:
 *  - $fillOpacity (/ca, 0..1) — alpha for fill operators (including image Do).
 *  - $strokeOpacity (/CA, 0..1) — alpha for stroke operators.
 *
 * Hashing: readonly VO; equality by content is delegated to the
 * caller (see Page::registerExtGState).
 */
final readonly class PdfExtGState
{
    public function __construct(
        public ?float $fillOpacity = null,
        public ?float $strokeOpacity = null,
    ) {}

    /**
     * Unique key for dedup within a page (one ExtGState per
     * comparable opacity tuple).
     */
    public function key(): string
    {
        return sprintf(
            'ca=%s,CA=%s',
            $this->fillOpacity === null ? '-' : (string) $this->fillOpacity,
            $this->strokeOpacity === null ? '-' : (string) $this->strokeOpacity,
        );
    }

    /**
     * Body for PDF object emission.
     */
    public function toDictBody(): string
    {
        $parts = ['/Type /ExtGState'];
        if ($this->fillOpacity !== null) {
            $parts[] = '/ca '.$this->formatNumber($this->fillOpacity);
        }
        if ($this->strokeOpacity !== null) {
            $parts[] = '/CA '.$this->formatNumber($this->strokeOpacity);
        }

        return '<< '.implode(' ', $parts).' >>';
    }

    private function formatNumber(float $n): string
    {
        if ($n === floor($n)) {
            return (string) (int) $n;
        }

        return rtrim(rtrim(sprintf('%.4F', $n), '0'), '.');
    }
}
