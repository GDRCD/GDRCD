<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Merge;

use Dskripchenko\PhpPdf\Pdf\Reader\ReaderDocument;

/**
 * An input document for {@see PdfMerger}, from a file path or raw bytes, with
 * an optional open password.
 */
final class PdfSource
{
    private ?ReaderDocument $document = null;

    private function __construct(
        private readonly string $bytes,
        private readonly string $password,
    ) {
    }

    public static function fromBytes(string $bytes, string $password = ''): self
    {
        return new self($bytes, $password);
    }

    public static function fromFile(string $path, string $password = ''): self
    {
        $bytes = @file_get_contents($path);
        if ($bytes === false) {
            throw new \RuntimeException("Cannot read PDF file: {$path}");
        }
        return new self($bytes, $password);
    }

    public function document(): ReaderDocument
    {
        return $this->document ??= ReaderDocument::fromBytes($this->bytes, $this->password);
    }
}
