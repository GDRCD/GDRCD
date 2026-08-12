<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * Minimal format-level PDF emitter (without layout/font logic).
 *
 * Pattern:
 *   $w = new Writer(version: '1.7');
 *   $catalogId = $w->reserveObject();
 *   $pagesId = $w->reserveObject();
 *   $pageId = $w->reserveObject();
 *   ...
 *   $w->setObject($catalogId, "<< /Type /Catalog /Pages $pagesId 0 R >>");
 *   ...
 *   $w->setRoot($catalogId);
 *   echo $w->toBytes();
 *
 * Resulting PDF structure:
 *   %PDF-1.7
 *   %ÂÅÒÓ          <- high-byte comment (ISO 32000-1 §7.5.2 recommendation:
 *                    helps detect binary mode during transmission)
 *   1 0 obj
 *     <<...>>
 *   endobj
 *   ...
 *   xref
 *     0 N
 *     0000000000 65535 f
 *     0000000018 00000 n
 *     ...
 *   trailer
 *     << /Size N /Root 1 0 R >>
 *   startxref
 *     <offset>
 *   %%EOF
 *
 * Indirect object IDs are issued monotonically starting from 1. Generation number
 * for all new objects = 0 (incremental updates are not supported).
 */
final class Writer
{
    private const LINE_ENDING = "\n";

    /** @var array<int, ?string> objectId -> serialised body (without `N 0 obj` wrapper) */
    private array $objects = [];

    private int $nextId = 1;

    private ?int $rootId = null;

    private ?int $infoId = null;

    /** Encryption config (null = no encryption). */
    private ?Encryption $encryption = null;

    private ?int $encryptId = null;

    /** PKCS#7 signing config (null = no signature). */
    private ?SignatureConfig $signature = null;

    private ?int $signatureDictId = null;

    /** Cached document file identifier (16-byte hex string). */
    private ?string $cachedFileIdHex = null;

    /**
     * @param  string  $version  PDF version header (e.g. '1.4', '1.7', '2.0').
     * @param  bool  $useXrefStream  Emit cross-reference table as XRef stream
     *                                object (PDF 1.5+) instead of classic
     *                                `xref...trailer` table. More compact
     *                                (binary packed + FlateDecode). Auto-disabled
     *                                for signed documents (PKCS#7 path keeps
     *                                classic xref for simpler /ByteRange patching).
     * @param  bool  $useObjectStreams  Pack non-stream uncompressed objects
     *                                   (catalog, page tree, info, etc.)
     *                                   into single compressed Object Stream (PDF 1.5+).
     *                                   Requires `useXrefStream: true` (objects use
     *                                   type-2 xref entries). Auto-disabled with
     *                                   encryption or signing.
     */
    public function __construct(
        private readonly string $version = '1.7',
        private readonly bool $useXrefStream = false,
        private readonly bool $useObjectStreams = false,
    ) {}

    /**
     * Compute deterministic document /ID hex string.
     *
     * Uses encryption fileId if encryption is configured. Otherwise MD5-derives
     * 16 bytes from concatenated object bodies, giving a stable per-document
     * fingerprint (same content -> same /ID across toBytes/toStream calls
     * on same Writer state).
     */
    private function fileIdHex(): string
    {
        if ($this->cachedFileIdHex !== null) {
            return $this->cachedFileIdHex;
        }
        if ($this->encryption !== null) {
            return $this->cachedFileIdHex = bin2hex($this->encryption->fileId);
        }
        // Deterministic hash from content for stable /ID.
        $material = '';
        foreach ($this->objects as $id => $body) {
            $material .= $id.':'.($body ?? '').'|';
        }
        $material .= 'root:'.($this->rootId ?? 0).':info:'.($this->infoId ?? 0);

        return $this->cachedFileIdHex = md5($material);
    }

    /**
     * Enable encryption — encrypt streams via RC4-128 V2 R3.
     */
    public function setEncryption(Encryption $encryption, int $encryptObjectId): void
    {
        $this->encryption = $encryption;
        $this->encryptId = $encryptObjectId;
    }

    /**
     * Enable PKCS#7 detached signing — patches /ByteRange and
     * /Contents placeholders in the signature dictionary post-emission.
     */
    public function setSignature(SignatureConfig $sig, int $sigDictId): void
    {
        $this->signature = $sig;
        $this->signatureDictId = $sigDictId;
    }

    /**
     * Reserve an object ID. Used when cross-references need to be created
     * before the object body (e.g. Catalog references Pages, which has
     * not been written yet).
     */
    public function reserveObject(): int
    {
        $id = $this->nextId++;
        $this->objects[$id] = null;

        return $id;
    }

    /**
     * Write the body of an already-reserved object.
     */
    public function setObject(int $id, string $body): void
    {
        if (! array_key_exists($id, $this->objects)) {
            throw new \LogicException("Object id $id was not reserved before setObject().");
        }
        $this->objects[$id] = $body;
    }

    /**
     * Append shortcut: reserves and fills in one call.
     */
    public function addObject(string $body): int
    {
        $id = $this->reserveObject();
        $this->setObject($id, $body);

        return $id;
    }

    public function setRoot(int $catalogId): void
    {
        $this->rootId = $catalogId;
    }

    /**
     * Sets /Info dictionary reference (PDF metadata). Trailer adds
     * `/Info N 0 R` when set.
     */
    public function setInfo(int $infoId): void
    {
        $this->infoId = $infoId;
    }

    /**
     * Serialise the entire PDF to a string. Throws if any objects are unfilled.
     *
     * Internally calls {@see toStream()} via php://memory.
     */
    public function toBytes(): string
    {
        $mem = fopen('php://memory', 'r+b');
        if ($mem === false) {
            throw new \RuntimeException('Failed to open php://memory stream');
        }
        try {
            $this->toStream($mem);
            rewind($mem);
            $bytes = stream_get_contents($mem);
            if ($bytes === false) {
                throw new \RuntimeException('Failed to read memory stream');
            }

            return $bytes;
        } finally {
            fclose($mem);
        }
    }

    /**
     * Streaming output. Writes PDF incrementally to $stream resource,
     * avoiding accumulating final document in string memory.
     *
     * Use case: writing large PDFs to file/HTTP response without full-document
     * memory overhead.
     *
     * @param  resource  $stream  any writable stream resource
     * @return int  total bytes written
     */
    public function toStream($stream): int
    {
        if (! is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new \InvalidArgumentException('toStream requires a stream resource');
        }
        if ($this->rootId === null) {
            throw new \LogicException('Catalog root not set; call setRoot() before toStream().');
        }
        foreach ($this->objects as $id => $body) {
            if ($body === null) {
                throw new \LogicException("Object $id was reserved but never filled.");
            }
        }

        // PKCS#7 signing requires post-emit byte patching (locate
        // /ByteRange + /Contents placeholders, sign hash, splice in
        // signature).
        //
        // Streaming optimization: if stream is seekable (file handle),
        // emit incrementally + seek back for patch. Avoids full document
        // buffer. Non-seekable streams (pipe, socket) fall back to
        // buffered approach.
        if ($this->signature !== null) {
            if (self::streamIsSeekable($stream)) {
                return $this->toSeekableStreamWithSignature($stream);
            }
            $bytes = $this->buildBytes();
            $bytes = $this->applyPkcs7Signature($bytes);

            return self::writeAll($stream, $bytes);
        }

        // Object Streams (PDF 1.5+): pack uncompressed dict objects
        // into single FlateDecode stream. Requires XRef stream (type-2 entries).
        // Disabled with encryption / signing for simplicity.
        $useObjStm = $this->useObjectStreams
            && $this->useXrefStream
            && $this->encryption === null
            && $this->signature === null;

        $written = 0;
        $written += self::writeAll($stream, '%PDF-' . $this->version . self::LINE_ENDING);
        $written += self::writeAll($stream, "%\xE2\xE3\xCF\xD3" . self::LINE_ENDING);

        $offsets = [];
        $compressedEntries = []; // id -> [objStreamId, indexInStream] for type-2 xref

        if ($useObjStm) {
            [$packed, $direct, $objStreamId, $objStreamBody] = $this->buildObjectStream();

            // Emit direct objects normally.
            foreach ($direct as $id => $body) {
                $offsets[$id] = $written;
                $written += self::writeAll($stream, $id . ' 0 obj' . self::LINE_ENDING);
                $written += self::writeAll($stream, $body . self::LINE_ENDING);
                $written += self::writeAll($stream, 'endobj' . self::LINE_ENDING);
            }

            // Emit object stream itself as a direct stream object.
            if ($objStreamBody !== null) {
                $offsets[$objStreamId] = $written;
                $written += self::writeAll($stream, $objStreamId . ' 0 obj' . self::LINE_ENDING);
                $written += self::writeAll($stream, $objStreamBody . self::LINE_ENDING);
                $written += self::writeAll($stream, 'endobj' . self::LINE_ENDING);
            }

            // Track compressed entries for xref.
            foreach ($packed as $index => $packedId) {
                $compressedEntries[$packedId] = [$objStreamId, $index];
            }
        } else {
            // Standard path: emit objects incrementally; record offsets for xref.
            foreach ($this->objects as $id => $body) {
                if ($this->encryption !== null && $id !== $this->encryptId) {
                    $body = $this->encryptStreamsInBody($body, $id);
                }
                $offsets[$id] = $written;
                $written += self::writeAll($stream, $id . ' 0 obj' . self::LINE_ENDING);
                $written += self::writeAll($stream, $body . self::LINE_ENDING);
                $written += self::writeAll($stream, 'endobj' . self::LINE_ENDING);
            }
        }

        if ($this->useXrefStream) {
            return $written + $this->writeXrefStream($stream, $written, $offsets, $compressedEntries);
        }

        // xref table.
        $xrefOffset = $written;
        $count = $this->nextId;
        $written += self::writeAll($stream, 'xref' . self::LINE_ENDING);
        $written += self::writeAll($stream, '0 ' . $count . self::LINE_ENDING);
        $written += self::writeAll($stream, '0000000000 65535 f ' . self::LINE_ENDING);
        for ($id = 1; $id < $count; $id++) {
            $written += self::writeAll($stream, sprintf("%010d 00000 n \n", $offsets[$id]));
        }

        // Trailer.
        $written += self::writeAll($stream, 'trailer' . self::LINE_ENDING);
        $infoPart = $this->infoId !== null ? ' /Info ' . $this->infoId . ' 0 R' : '';
        $encryptPart = '';
        if ($this->encryption !== null && $this->encryptId !== null) {
            $encryptPart = ' /Encrypt ' . $this->encryptId . ' 0 R';
        }
        // Always emit /ID (encryption uses its own fileId,
        // others get auto-generated random fingerprint).
        $idPart = ' /ID [<'.$this->fileIdHex().'> <'.$this->fileIdHex().'>]';
        $written += self::writeAll($stream, '<< /Size ' . $count . ' /Root ' . $this->rootId . ' 0 R' . $infoPart . $encryptPart . $idPart . ' >>' . self::LINE_ENDING);
        $written += self::writeAll($stream, 'startxref' . self::LINE_ENDING);
        $written += self::writeAll($stream, $xrefOffset . self::LINE_ENDING);
        $written += self::writeAll($stream, '%%EOF' . self::LINE_ENDING);

        return $written;
    }

    /**
     * Partition objects into "packable" (can go into an Object Stream)
     * and "direct" (streams, encryption dict, signature dict).
     *
     * Spec §7.5.7: object stream may contain only uncompressed direct
     * objects without stream payload, generation 0.
     *
     * Heuristic for detecting stream object: look for `\nstream\n` substring
     * in body (reliable signal — all stream objects contain this marker).
     *
     * @return array{0: list<int>, 1: array<int, string>, 2: int, 3: ?string}
     *   [list of packed object IDs (in order), direct objects (id->body),
     *    objStreamId, compressed object stream body or null if nothing to pack]
     */
    private function buildObjectStream(): array
    {
        $packable = [];
        $direct = [];

        foreach ($this->objects as $id => $body) {
            if ($body === null) {
                continue;
            }
            if ($id === $this->encryptId
                || $id === $this->signatureDictId
                || str_contains($body, "\nstream\n")
                || str_starts_with($body, "stream\n")
            ) {
                $direct[$id] = $body;
            } else {
                $packable[$id] = $body;
            }
        }

        // If packable too few, overhead > savings; fall back to all-direct.
        if (count($packable) < 3) {
            return [[], $this->objects, $this->nextId, null];
        }

        // Allocate object stream ID (consumes nextId slot).
        $objStreamId = $this->nextId;
        $this->nextId++;

        // Build object stream content: header (id offset pairs) + bodies.
        $header = '';
        $bodies = '';
        $bodyOffset = 0;
        $packedIds = [];
        foreach ($packable as $id => $body) {
            $packedIds[] = $id;
            $header .= $id.' '.$bodyOffset.' ';
            $bodies .= $body.self::LINE_ENDING;
            $bodyOffset += strlen($body) + strlen(self::LINE_ENDING);
        }
        $header = rtrim($header);
        $first = strlen($header) + strlen(self::LINE_ENDING);
        $content = $header.self::LINE_ENDING.$bodies;

        $compressed = gzcompress($content, 9);
        if ($compressed === false) {
            throw new \RuntimeException('Failed to compress object stream');
        }

        $n = count($packable);
        $dict = '<< /Type /ObjStm /N '.$n.' /First '.$first
            .' /Length '.strlen($compressed).' /Filter /FlateDecode >>';
        $objStreamBody = $dict.self::LINE_ENDING.'stream'.self::LINE_ENDING
            .$compressed.self::LINE_ENDING.'endstream';

        return [$packedIds, $direct, $objStreamId, $objStreamBody];
    }

    /**
     * Emit cross-reference table as XRef stream object (PDF 1.5+).
     *
     * Replaces classic `xref...trailer` keywords with binary-packed FlateDecode
     * stream object. Entries packed as type(1) + offset(4) + gen(2) bytes.
     * The xref stream object receives its own id = current nextId, is registered
     * in /Size, and its own entry is included in the table.
     *
     * Supports type-2 entries for objects packed into an Object Stream.
     * Type 2 entry: type(1)=2 + objStmId(4 bytes) + indexInStream(2 bytes).
     *
     * @param  resource  $stream
     * @param  array<int, int>  $offsets
     * @param  array<int, array{0: int, 1: int}>  $compressedEntries  id -> [objStreamId, index]
     */
    private function writeXrefStream($stream, int $startWritten, array $offsets, array $compressedEntries = []): int
    {
        $xrefObjId = $this->nextId;
        $xrefOffset = $startWritten;
        // Include xref stream object's own offset (it will be at $startWritten).
        $offsets[$xrefObjId] = $xrefOffset;
        $sizeCount = $xrefObjId + 1; // entries 0..xrefObjId inclusive

        // W = [1 4 2]: type(1 byte) + offset/objStmId(4 bytes) + gen/index(2 bytes).
        $entries = '';
        // Entry 0: free, offset 0, gen 65535.
        $entries .= chr(0).pack('N', 0).pack('n', 65535);
        for ($id = 1; $id < $sizeCount; $id++) {
            if (isset($compressedEntries[$id])) {
                // Type 2: compressed — refers to ObjStm + index.
                [$objStmId, $index] = $compressedEntries[$id];
                $entries .= chr(2).pack('N', $objStmId).pack('n', $index);
            } else {
                $off = $offsets[$id] ?? 0;
                $entries .= chr(1).pack('N', $off).pack('n', 0);
            }
        }

        // FlateDecode compress.
        $compressed = gzcompress($entries, 9);
        if ($compressed === false) {
            throw new \RuntimeException('Failed to compress xref stream');
        }
        $streamLen = strlen($compressed);

        // Build dictionary.
        $dict = '<< /Type /XRef';
        $dict .= ' /Size '.$sizeCount;
        $dict .= ' /Root '.$this->rootId.' 0 R';
        if ($this->infoId !== null) {
            $dict .= ' /Info '.$this->infoId.' 0 R';
        }
        if ($this->encryption !== null && $this->encryptId !== null) {
            $dict .= ' /Encrypt '.$this->encryptId.' 0 R';
        }
        // Always emit /ID (PDF 1.5 XRef stream).
        $dict .= ' /ID [<'.$this->fileIdHex().'> <'.$this->fileIdHex().'>]';
        $dict .= ' /W [1 4 2]';
        $dict .= ' /Filter /FlateDecode';
        $dict .= ' /Length '.$streamLen;
        $dict .= ' >>';

        $written = 0;
        $written += self::writeAll($stream, $xrefObjId.' 0 obj'.self::LINE_ENDING);
        $written += self::writeAll($stream, $dict.self::LINE_ENDING);
        $written += self::writeAll($stream, 'stream'.self::LINE_ENDING);
        $written += self::writeAll($stream, $compressed.self::LINE_ENDING);
        $written += self::writeAll($stream, 'endstream'.self::LINE_ENDING);
        $written += self::writeAll($stream, 'endobj'.self::LINE_ENDING);
        $written += self::writeAll($stream, 'startxref'.self::LINE_ENDING);
        $written += self::writeAll($stream, $xrefOffset.self::LINE_ENDING);
        $written += self::writeAll($stream, '%%EOF'.self::LINE_ENDING);

        return $written;
    }

    /**
     * Internal: build PDF in a memory string without signing. Used by the
     * signing path which needs full bytes for post-emit patching.
     */
    private function buildBytes(): string
    {
        $mem = fopen('php://memory', 'r+b');
        if ($mem === false) {
            throw new \RuntimeException('Failed to open memory stream');
        }
        try {
            $hadSig = $this->signature;
            $this->signature = null; // temporarily disable to avoid recursion
            $this->toStream($mem);
            $this->signature = $hadSig;
            rewind($mem);

            return (string) stream_get_contents($mem);
        } finally {
            fclose($mem);
        }
    }

    /**
     * @param  resource  $stream
     */
    /**
     * Detect whether stream supports random-access seek (fseek).
     * File handles are seekable; pipes/sockets/php://output usually are not.
     *
     * @param  resource  $stream
     */
    private static function streamIsSeekable($stream): bool
    {
        $meta = stream_get_meta_data($stream);

        return ($meta['seekable'] ?? false) === true;
    }

    /**
     * Streaming PKCS#7 signing for seekable streams.
     * Emits objects incrementally, then after the full emit seeks back and
     * patches /ByteRange + /Contents placeholders with actual signature.
     *
     * @param  resource  $stream
     */
    private function toSeekableStreamWithSignature($stream): int
    {
        // Emit identical to non-signed path, then patch in-place in stream.
        // Strategy: write everything, remember start offset, seek back, patch.
        $startOffset = ftell($stream);
        $written = 0;
        $written += self::writeAll($stream, '%PDF-' . $this->version . self::LINE_ENDING);
        $written += self::writeAll($stream, "%\xE2\xE3\xCF\xD3" . self::LINE_ENDING);

        $offsets = [];
        foreach ($this->objects as $id => $body) {
            if ($body === null) {
                throw new \LogicException("Object $id was reserved but never filled.");
            }
            if ($this->encryption !== null && $id !== $this->encryptId) {
                $body = $this->encryptStreamsInBody($body, $id);
            }
            $offsets[$id] = $written;
            $written += self::writeAll($stream, $id . ' 0 obj' . self::LINE_ENDING);
            $written += self::writeAll($stream, $body . self::LINE_ENDING);
            $written += self::writeAll($stream, 'endobj' . self::LINE_ENDING);
        }

        $xrefOffset = $written;
        $count = $this->nextId;
        $written += self::writeAll($stream, 'xref' . self::LINE_ENDING);
        $written += self::writeAll($stream, '0 ' . $count . self::LINE_ENDING);
        $written += self::writeAll($stream, '0000000000 65535 f ' . self::LINE_ENDING);
        for ($id = 1; $id < $count; $id++) {
            $off = $offsets[$id] ?? 0;
            $written += self::writeAll($stream, sprintf('%010d 00000 n %s', $off, self::LINE_ENDING));
        }
        // Trailer.
        $trailer = '<< /Size ' . $count . ' /Root 1 0 R';
        if ($this->encryption !== null && $this->encryptId !== null) {
            $trailer .= ' /Encrypt ' . $this->encryptId . ' 0 R';
        }
        // Always emit /ID, derived deterministically from the encryption
        // fileId or hashed content (see fileIdHex()).
        $trailer .= ' /ID [<'.$this->fileIdHex().'> <'.$this->fileIdHex().'>]';
        $trailer .= ' >>';
        $written += self::writeAll($stream, 'trailer' . self::LINE_ENDING);
        $written += self::writeAll($stream, $trailer . self::LINE_ENDING);
        $written += self::writeAll($stream, 'startxref' . self::LINE_ENDING);
        $written += self::writeAll($stream, $xrefOffset . self::LINE_ENDING);
        $written += self::writeAll($stream, '%%EOF' . self::LINE_ENDING);

        // Now patch signature: seek to start, read full content, apply
        // applyPkcs7Signature, seek back, rewrite content.
        // Note: for very large documents this optimization is partial — final
        // hashing still needs full read. But avoiding intermediate string
        // concatenation saves memory bandwidth.
        fseek($stream, $startOffset);
        $content = '';
        while (! feof($stream)) {
            $chunk = fread($stream, 65536);
            if ($chunk === false) {
                break;
            }
            $content .= $chunk;
        }
        $signed = $this->applyPkcs7Signature($content);
        // Rewrite signed content.
        fseek($stream, $startOffset);
        $writtenFinal = self::writeAll($stream, $signed);
        // Truncate if signed content shorter than original.
        $endOffset = $startOffset + $writtenFinal;
        if (function_exists('ftruncate')) {
            ftruncate($stream, $endOffset);
        }

        return $writtenFinal;
    }

    private static function writeAll($stream, string $data): int
    {
        $total = strlen($data);
        $written = 0;
        while ($written < $total) {
            $w = fwrite($stream, substr($data, $written));
            if ($w === false || $w === 0) {
                throw new \RuntimeException('Stream write failed at offset ' . $written);
            }
            $written += $w;
        }

        return $total;
    }

    /**
     * Locate placeholders in assembled bytes, compute byte range, sign
     * data outside /Contents, patch dictionary in-place.
     */
    private function applyPkcs7Signature(string $bytes): string
    {
        // Locate /ByteRange [<digits> <digits> <digits> <digits>] placeholder.
        // The placeholder values are all zeros, padded with trailing spaces to
        // 10 digits each, leaving room for actual integers up to ~10 GB files.
        if (! preg_match(
            '@/ByteRange \[(\d+ +\d+ +\d+ +\d+) +\]@',
            $bytes,
            $brMatch,
            PREG_OFFSET_CAPTURE,
        )) {
            throw new \RuntimeException('/ByteRange placeholder not found in PDF stream');
        }
        $brStart = (int) $brMatch[0][1];
        $brLen = strlen((string) $brMatch[0][0]);

        // Locate /Contents <000...000> placeholder.
        if (! preg_match(
            '@/Contents <0+>@',
            $bytes,
            $cMatch,
            PREG_OFFSET_CAPTURE,
        )) {
            throw new \RuntimeException('/Contents placeholder not found');
        }
        $cStart = (int) $cMatch[0][1];
        $cLen = strlen((string) $cMatch[0][0]);

        // Byte ranges: [0, gapStart] and [gapEnd, EOF].
        // gapStart = '<' position; gapEnd = position right after '>'.
        $gapStart = $cStart + strlen('/Contents ');
        $gapEnd = $cStart + $cLen;
        $a = 0;
        $b = $gapStart;
        $c = $gapEnd;
        $d = strlen($bytes) - $gapEnd;

        // Compute ByteRange string padded to original length.
        $brContent = sprintf('/ByteRange [%d %d %d %d]', $a, $b, $c, $d);
        if (strlen($brContent) > $brLen) {
            throw new \RuntimeException('/ByteRange replacement exceeds placeholder');
        }
        $brContent = str_pad($brContent, $brLen, ' ', STR_PAD_RIGHT);
        $bytes = substr_replace($bytes, $brContent, $brStart, $brLen);

        // Build hashable data: bytes[0..b] + bytes[c..].
        $signedData = substr($bytes, 0, $b) . substr($bytes, $c);

        // Sign through openssl_pkcs7_sign — requires input file.
        $infile = tempnam(sys_get_temp_dir(), 'phppdf-sig-in-');
        $outfile = tempnam(sys_get_temp_dir(), 'phppdf-sig-out-');
        if ($infile === false || $outfile === false) {
            throw new \RuntimeException('Failed to create temp files for signing');
        }
        file_put_contents($infile, $signedData);

        $signKey = $this->signature->privateKeyPassphrase !== null
            ? [$this->signature->privateKeyPem, $this->signature->privateKeyPassphrase]
            : $this->signature->privateKeyPem;

        $ok = openssl_pkcs7_sign(
            $infile,
            $outfile,
            $this->signature->certPem,
            $signKey,
            [],
            PKCS7_BINARY | PKCS7_DETACHED,
        );
        $smime = $ok ? (string) file_get_contents($outfile) : '';
        @unlink($infile);
        @unlink($outfile);
        if (! $ok) {
            throw new \RuntimeException('openssl_pkcs7_sign failed: ' . openssl_error_string());
        }

        // Extract DER PKCS#7 bytes from S/MIME envelope.
        $der = self::extractPkcs7FromSmime($smime);
        $sigHex = strtoupper(bin2hex($der));

        $placeholderHexLen = $cLen - strlen('/Contents <') - 1; // minus '>'
        if (strlen($sigHex) > $placeholderHexLen) {
            throw new \RuntimeException(sprintf(
                'PKCS#7 signature %d hex chars exceeds placeholder %d',
                strlen($sigHex), $placeholderHexLen,
            ));
        }
        $sigHex = str_pad($sigHex, $placeholderHexLen, '0', STR_PAD_RIGHT);

        // Patch /Contents <...>.
        $hexStart = $cStart + strlen('/Contents <');

        return substr_replace($bytes, $sigHex, $hexStart, $placeholderHexLen);
    }

    /**
     * Parse openssl_pkcs7_sign output (S/MIME multipart/signed) → raw DER.
     *
     * Structure (simplified):
     *   MIME headers...
     *   --boundary
     *   <signed message body>
     *   --boundary
     *   Content-Type: application/x-pkcs7-signature; ...
     *   Content-Transfer-Encoding: base64
     *   Content-Disposition: attachment; filename="smime.p7s"
     *   <empty>
     *   <base64-encoded PKCS#7>
     *   --boundary--
     */
    private static function extractPkcs7FromSmime(string $smime): string
    {
        if (! preg_match(
            '@Content-Disposition:[^\r\n]+(?:\r?\n[^\r\n]*)*?\r?\n\r?\n([A-Za-z0-9+/=\s]+?)\r?\n--@s',
            $smime,
            $m,
        )) {
            throw new \RuntimeException('PKCS#7 base64 portion not found in S/MIME');
        }
        $b64 = (string) preg_replace('@\s+@', '', $m[1]);
        $der = base64_decode($b64, true);
        if ($der === false || $der === '') {
            throw new \RuntimeException('base64_decode failed for PKCS#7 envelope');
        }

        return $der;
    }

    /**
     * Encrypt streams and strings in an object body.
     * Streams: `stream\n<bytes>\nendstream` -> RC4/AES per-object.
     * Strings: literal `(...)` -> encrypted, then re-encoded.
     */
    private function encryptStreamsInBody(string $body, int $objId): string
    {
        if ($this->encryption === null) {
            return $body;
        }
        $enc = $this->encryption;

        // Encrypt literal strings first (before streams, so the regex is
        // not corrupted by binary content).
        $body = $this->encryptLiteralStrings($body, $objId);

        // Encrypt stream content: `stream\n<bytes>\nendstream`.
        return preg_replace_callback(
            '@stream\n(.*?)\nendstream@s',
            function (array $m) use ($enc, $objId): string {
                $encrypted = $enc->encryptObject($m[1], $objId);

                return 'stream'."\n".$encrypted."\n".'endstream';
            },
            $body,
        ) ?? $body;
    }

    /**
     * Encrypt literal PDF strings (...) -> output as hex form
     * <ENCRYPTED_HEX>. Walks body manually for correct paren balance
     * (literal strings allow nested () pairs balanced).
     */
    private function encryptLiteralStrings(string $body, int $objId): string
    {
        if ($this->encryption === null) {
            return $body;
        }
        $enc = $this->encryption;
        $out = '';
        $len = strlen($body);
        $i = 0;
        while ($i < $len) {
            $ch = $body[$i];
            if ($ch === '(') {
                // Find matching closing paren respecting nested + escaped.
                $depth = 1;
                $j = $i + 1;
                $strStart = $j;
                while ($j < $len && $depth > 0) {
                    $cj = $body[$j];
                    if ($cj === '\\' && $j + 1 < $len) {
                        $j += 2;

                        continue;
                    }
                    if ($cj === '(') {
                        $depth++;
                    } elseif ($cj === ')') {
                        $depth--;
                        if ($depth === 0) {
                            break;
                        }
                    }
                    $j++;
                }
                if ($depth !== 0) {
                    // Malformed — keep as-is.
                    $out .= substr($body, $i, $j - $i + 1);
                    $i = $j + 1;

                    continue;
                }
                $raw = substr($body, $strStart, $j - $strStart);
                // Unescape PDF literal string escapes: \\, \(, \).
                $decoded = strtr($raw, ['\\\\' => '\\', '\\(' => '(', '\\)' => ')']);
                $encrypted = $enc->encryptObject($decoded, $objId);
                $out .= '<'.bin2hex($encrypted).'>';
                $i = $j + 1; // skip closing )
            } else {
                $out .= $ch;
                $i++;
            }
        }

        return $out;
    }
}
