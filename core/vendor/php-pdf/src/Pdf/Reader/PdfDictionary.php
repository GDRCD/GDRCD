<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * A PDF dictionary `<< ... >>` (ISO 32000-1 §7.3.7).
 *
 * Keys are name strings without the leading slash. Values are any parsed
 * object: scalar, {@see PdfName}, {@see PdfString}, {@see PdfReference},
 * {@see PdfDictionary}, {@see PdfStream}, {@see PdfNull}, or a PHP list array.
 */
final readonly class PdfDictionary
{
    /** @param array<string,mixed> $items */
    public function __construct(public array $items = [])
    {
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function get(string $key): mixed
    {
        return $this->items[$key] ?? null;
    }

    /** @return array<string,mixed> */
    public function all(): array
    {
        return $this->items;
    }
}
