<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Merge;

use Dskripchenko\PhpPdf\Pdf\Reader\PdfDictionary;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfNull;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfReference;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfStream;
use Dskripchenko\PhpPdf\Pdf\Reader\ReaderDocument;

/**
 * Copies object subtrees from one or more source documents into a single flat
 * object set, assigning fresh sequential IDs and rewriting every indirect
 * reference to its new ID.
 *
 * Streams are copied verbatim (their still-encoded body is preserved); the
 * serializer restates `/Length` from the actual byte count, which also
 * corrects streams whose source was encrypted (AES padding changes the size).
 *
 * A per-source dedup map preserves object sharing and breaks reference cycles:
 * a new ID is reserved *before* the object's contents are imported, so a cycle
 * back to it resolves to that reserved ID.
 */
final class ObjectImporter
{
    /** @var array<int,mixed> newId → rewritten value */
    private array $objects = [];

    private int $nextId = 1;

    /** @var array<int,int> source object number → newId, for the current source */
    private array $sourceMap = [];

    private ?ReaderDocument $source = null;

    /**
     * Switch the active source. Object-number dedup is scoped per source, since
     * numbers collide across documents.
     */
    public function useSource(ReaderDocument $source): void
    {
        $this->source = $source;
        $this->sourceMap = [];
    }

    /**
     * Reserve the next object ID and store a value at it directly (used for the
     * merger's own catalog / page-tree nodes).
     *
     * @return int the assigned newId
     */
    public function allocate(mixed $value): int
    {
        $id = $this->nextId++;
        $this->objects[$id] = $value;
        return $id;
    }

    public function set(int $id, mixed $value): void
    {
        $this->objects[$id] = $value;
    }

    public function get(int $id): mixed
    {
        return $this->objects[$id] ?? null;
    }

    /**
     * Import the source object with the given number, returning a reference to
     * its new ID (deduplicated within the current source).
     */
    public function importObject(int $sourceNumber): PdfReference
    {
        if (isset($this->sourceMap[$sourceNumber])) {
            return new PdfReference($this->sourceMap[$sourceNumber], 0);
        }
        if ($this->source === null) {
            throw new \LogicException('ObjectImporter::useSource() must be called first');
        }

        $id = $this->nextId++;
        $this->sourceMap[$sourceNumber] = $id;
        $this->objects[$id] = PdfNull::instance(); // placeholder for cycles

        $this->objects[$id] = $this->importObjectBody($this->source->getObject($sourceNumber));

        return new PdfReference($id, 0);
    }

    /**
     * Import the body of a top-level object *in place* at its reserved ID.
     *
     * Crucially, a stream stays a stream (it already owns this object ID) — it
     * must NOT be routed through {@see importValue()}, which re-allocates a
     * fresh object for a stream and returns a reference to it. Doing so would
     * leave the reserved ID holding a mere `N G R` (a reference-to-a-reference)
     * — which real PDF readers reject on e.g. /Contents ("weird page
     * contents", blank pages), even though a lenient reader dereferences it.
     */
    private function importObjectBody(mixed $value): mixed
    {
        if ($value instanceof PdfStream) {
            return new PdfStream($this->importDictionary($value->dict), $value->raw);
        }
        if ($value instanceof PdfDictionary) {
            return $this->importDictionary($value);
        }
        if (is_array($value)) {
            return array_map(fn ($v) => $this->importValue($v), $value);
        }
        if ($value instanceof PdfReference) {
            return $this->importObject($value->number);
        }
        return $value;
    }

    /**
     * Deep-copy a value nested inside a dictionary or array, importing any
     * references it contains and promoting any inline stream to its own
     * indirect object (streams must be indirect).
     */
    public function importValue(mixed $value): mixed
    {
        if ($value instanceof PdfReference) {
            return $this->importObject($value->number);
        }
        if ($value instanceof PdfStream) {
            $id = $this->allocate(PdfNull::instance());
            $this->objects[$id] = new PdfStream(
                $this->importDictionary($value->dict),
                $value->raw,
            );
            return new PdfReference($id, 0);
        }
        if ($value instanceof PdfDictionary) {
            return $this->importDictionary($value);
        }
        if (is_array($value)) {
            return array_map(fn ($v) => $this->importValue($v), $value);
        }
        return $value;
    }

    private function importDictionary(PdfDictionary $dict): PdfDictionary
    {
        $items = [];
        foreach ($dict->all() as $key => $value) {
            $items[$key] = $this->importValue($value);
        }
        return new PdfDictionary($items);
    }

    /**
     * All imported and allocated objects, keyed by newId (contiguous from 1).
     *
     * @return array<int,mixed>
     */
    public function objects(): array
    {
        return $this->objects;
    }

    public function count(): int
    {
        return $this->nextId - 1;
    }
}
