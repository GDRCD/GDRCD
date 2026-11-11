<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * Result of reading the cross-reference chain.
 *
 * Uncompressed objects are located by byte offset ({@see $offsets}); objects
 * stored inside object streams (§7.5.7) are located by their containing stream
 * and index ({@see $compressed}). The newest trailer carries /Root, /Encrypt,
 * /ID, /Size.
 */
final readonly class XrefTable
{
    /**
     * @param array<int,int>            $offsets      object number → byte offset (type 1)
     * @param array<int,int>            $generations  object number → generation (type 1)
     * @param array<int,array{int,int}> $compressed   object number → [object-stream number, index] (type 2)
     */
    public function __construct(
        public array $offsets,
        public array $generations,
        public PdfDictionary $trailer,
        public array $compressed = [],
    ) {
    }

    public function hasObject(int $number): bool
    {
        return array_key_exists($number, $this->offsets)
            || array_key_exists($number, $this->compressed);
    }

    public function offsetOf(int $number): ?int
    {
        return $this->offsets[$number] ?? null;
    }

    /** @return array{int,int}|null [object-stream number, index] */
    public function compressedOf(int $number): ?array
    {
        return $this->compressed[$number] ?? null;
    }
}
