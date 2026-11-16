<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * A parsed, navigable view over an existing PDF file.
 *
 * Objects are resolved lazily from their cross-reference offsets and cached.
 * Reference chains are dereferenced with a cycle guard. Classic tables, XRef
 * and object streams, corrupt-xref recovery, and standard-handler decryption
 * are all resolved behind this surface.
 */
final class ReaderDocument
{
    /** @var array<int,mixed> objnum → resolved value */
    private array $cache = [];

    /** @var array<int,array{data:string, offsets:array<int,int>, first:int}> objstm number → parsed header */
    private array $objStmCache = [];

    private ?Decryptor $decryptor = null;

    /** Object number of the /Encrypt dictionary, which is never itself decrypted. */
    private ?int $encryptObjNum = null;

    private function __construct(
        private readonly string $data,
        private readonly XrefTable $xref,
        private readonly string $password = '',
    ) {
        $this->initDecryption();
    }

    public static function fromBytes(string $data, string $password = ''): self
    {
        try {
            $doc = new self($data, (new XrefReader($data))->read(), $password);
            if ($doc->isNavigable()) {
                return $doc;
            }
        } catch (PdfParseException) {
            // Fall through to recovery.
        }

        return new self($data, (new XrefRecovery($data))->rebuild(), $password);
    }

    /**
     * Build a document over an already-computed cross-reference table
     * (used by the recovery scanner to bootstrap object-stream indexing).
     */
    public static function fromXref(string $data, XrefTable $xref, string $password = ''): self
    {
        return new self($data, $xref, $password);
    }

    /**
     * Set up the decryptor from /Encrypt, if present. Runs before any object is
     * decrypted, so the /Encrypt dictionary and /O,/U strings are read as-is.
     */
    private function initDecryption(): void
    {
        $enc = $this->trailer()->get('Encrypt');
        if ($enc === null || $enc instanceof PdfNull) {
            return;
        }
        if ($enc instanceof PdfReference) {
            $this->encryptObjNum = $enc->number;
        }
        $encDict = $this->deref($enc);
        if (!$encDict instanceof PdfDictionary) {
            return;
        }

        $idFirst = '';
        $id = $this->trailer()->get('ID');
        if (is_array($id) && isset($id[0])) {
            $first = $this->deref($id[0]);
            if ($first instanceof PdfString) {
                $idFirst = $first->bytes;
            }
        }

        $this->decryptor = Decryptor::create($encDict, $idFirst, $this->password);
    }

    /**
     * True when the catalog and page-tree root resolve to dictionaries — the
     * cheap probe that decides whether a parsed xref is trustworthy or the
     * recovery scanner should take over.
     */
    private function isNavigable(): bool
    {
        try {
            $catalog = $this->deref($this->trailer()->get('Root'));
            if (!$catalog instanceof PdfDictionary) {
                return false;
            }
            return $this->deref($catalog->get('Pages')) instanceof PdfDictionary;
        } catch (PdfParseException) {
            return false;
        }
    }

    public function trailer(): PdfDictionary
    {
        return $this->xref->trailer;
    }

    /**
     * Resolve an indirect object by number, or return {@see PdfNull} when the
     * object is absent from the cross-reference table.
     */
    public function getObject(int $number): mixed
    {
        if (array_key_exists($number, $this->cache)) {
            return $this->cache[$number];
        }

        $offset = $this->xref->offsetOf($number);
        if ($offset !== null) {
            $parsed = (new ObjectParser(new Lexer($this->data, $offset)))->parseIndirectObject();
            $value = $parsed['value'];
            if ($this->decryptor !== null && $number !== $this->encryptObjNum) {
                $gen = $this->xref->generations[$number] ?? 0;
                $value = $this->decryptValue($value, $number, $gen);
            }
            return $this->cache[$number] = $value;
        }

        $loc = $this->xref->compressedOf($number);
        if ($loc !== null) {
            // Members of an object stream are already plaintext once the stream
            // (a top-level object) has been decrypted — no per-object step.
            return $this->cache[$number] = $this->getCompressedObject($number, $loc[0], $loc[1]);
        }

        return PdfNull::instance();
    }

    /**
     * Recursively decrypt the strings (and, for streams, the body) of a
     * top-level object using its own number/generation as the key salt.
     */
    private function decryptValue(mixed $value, int $objNum, int $gen): mixed
    {
        if ($this->decryptor === null) {
            return $value;
        }
        if ($value instanceof PdfString) {
            return new PdfString($this->decryptor->decryptString($value->bytes, $objNum, $gen), $value->hex);
        }
        if ($value instanceof PdfStream) {
            $dict = $this->decryptValue($value->dict, $objNum, $gen);
            $raw = $this->decryptor->decryptStream($value->raw, $objNum, $gen);
            return new PdfStream($dict instanceof PdfDictionary ? $dict : $value->dict, $raw);
        }
        if ($value instanceof PdfDictionary) {
            $items = [];
            foreach ($value->all() as $k => $v) {
                $items[$k] = $this->decryptValue($v, $objNum, $gen);
            }
            return new PdfDictionary($items);
        }
        if (is_array($value)) {
            return array_map(fn ($v) => $this->decryptValue($v, $objNum, $gen), $value);
        }
        return $value;
    }

    /**
     * Resolve an object stored inside an object stream (§7.5.7).
     *
     * @param int $streamNumber the containing /ObjStm object
     * @param int $index        the object's position within that stream
     */
    private function getCompressedObject(int $number, int $streamNumber, int $index): mixed
    {
        $header = $this->objStmCache[$streamNumber] ?? null;
        if ($header === null) {
            $stream = $this->getObject($streamNumber);
            if (!$stream instanceof PdfStream) {
                return PdfNull::instance();
            }
            $data = $this->streamData($stream);
            $n = $this->deref($stream->dict->get('N'));
            $first = $this->deref($stream->dict->get('First'));
            if (!is_int($n) || !is_int($first)) {
                return PdfNull::instance();
            }

            // Header: N pairs of (object-number, relative-offset).
            $lexer = new Lexer($data, 0);
            $offsets = [];
            for ($i = 0; $i < $n; $i++) {
                $numTok = $lexer->nextToken();
                $offTok = $lexer->nextToken();
                if ($numTok->type !== TokenType::Number || $offTok->type !== TokenType::Number) {
                    break;
                }
                $offsets[$i] = (int) $offTok->value;
            }

            $header = ['data' => $data, 'offsets' => $offsets, 'first' => $first];
            $this->objStmCache[$streamNumber] = $header;
        }

        if (!array_key_exists($index, $header['offsets'])) {
            return PdfNull::instance();
        }

        $start = $header['first'] + $header['offsets'][$index];
        return (new ObjectParser(new Lexer($header['data'], $start)))->parseValue();
    }

    /**
     * Follow a chain of indirect references to the concrete value it points at.
     */
    public function deref(mixed $value): mixed
    {
        $guard = [];
        while ($value instanceof PdfReference) {
            $key = $value->number;
            if (isset($guard[$key])) {
                return PdfNull::instance(); // reference cycle
            }
            $guard[$key] = true;
            $value = $this->getObject($value->number);
        }
        return $value;
    }

    /**
     * Fully decode a stream's `/Filter` chain into its plain bytes.
     */
    public function streamData(PdfStream $stream): string
    {
        return (new StreamDecoder($this))->decode($stream);
    }

    /**
     * The document catalog (`/Root`).
     */
    public function catalog(): PdfDictionary
    {
        $root = $this->deref($this->trailer()->get('Root'));
        if (!$root instanceof PdfDictionary) {
            throw new PdfParseException('Document catalog (/Root) is missing or invalid');
        }
        return $root;
    }

    /** @var list<ReaderPage>|null */
    private ?array $pagesCache = null;

    /** @var array<string,mixed>|null name → destination value */
    private ?array $namedDestCache = null;

    /**
     * The document's leaf pages in reading order, with inheritable attributes
     * flattened.
     *
     * @return list<ReaderPage>
     */
    public function pages(): array
    {
        return $this->pagesCache ??= (new PageTree($this))->pages();
    }

    /**
     * Flattened named destinations (§12.3.2.3): the legacy `/Dests` dictionary
     * and the `/Names /Dests` name tree, merged into a name → destination map.
     * Values are the raw destination (an explicit array, or a `<< /D … >>`
     * dictionary) — resolution to a page is the caller's job.
     *
     * @return array<string,mixed>
     */
    public function namedDestinations(): array
    {
        if ($this->namedDestCache !== null) {
            return $this->namedDestCache;
        }

        $out = [];
        $catalog = $this->catalog();

        // Legacy /Dests dictionary (PDF 1.1): name → destination.
        $dests = $this->deref($catalog->get('Dests'));
        if ($dests instanceof PdfDictionary) {
            foreach ($dests->all() as $name => $value) {
                $out[$name] = $value;
            }
        }

        // Name tree /Names /Dests (PDF 1.2+).
        $names = $this->deref($catalog->get('Names'));
        if ($names instanceof PdfDictionary) {
            $this->walkNameTree($this->deref($names->get('Dests')), $out, 0);
        }

        return $this->namedDestCache = $out;
    }

    /**
     * @param array<string,mixed> $out
     */
    private function walkNameTree(mixed $node, array &$out, int $depth): void
    {
        if ($depth > 64 || !$node instanceof PdfDictionary) {
            return;
        }
        $names = $this->deref($node->get('Names'));
        if (is_array($names)) {
            for ($i = 0, $n = count($names); $i + 1 < $n; $i += 2) {
                $key = $this->deref($names[$i]);
                if ($key instanceof PdfString) {
                    $out[$key->bytes] = $names[$i + 1];
                }
            }
        }
        $kids = $this->deref($node->get('Kids'));
        if (is_array($kids)) {
            foreach ($kids as $kid) {
                $this->walkNameTree($this->deref($kid), $out, $depth + 1);
            }
        }
    }

    /**
     * Number of pages in the document (authoritative leaf-walk count).
     */
    public function pageCount(): int
    {
        return count($this->pages());
    }
}
