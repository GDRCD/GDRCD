<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * Rebuilds the cross-reference table by scanning the raw file for `N G obj`
 * headers when the real xref is missing or corrupt (ISO 32000-1 §7.5.4 note).
 *
 * Scanned in file order so later (incrementally updated) definitions overwrite
 * earlier ones. Object streams found during the scan are opened and their
 * contained objects indexed, so recovery also works for PDF 1.5+ files whose
 * catalog lives inside an /ObjStm.
 */
final class XrefRecovery
{
    public function __construct(private readonly string $data)
    {
    }

    public function rebuild(): XrefTable
    {
        [$offsets, $gens] = $this->scanObjects();

        // Bootstrap a reader over the offset-only table to open object streams
        // and cross-reference streams for /Root discovery.
        $prelim = new XrefTable($offsets, $gens, new PdfDictionary([]), []);
        $tmp = ReaderDocument::fromXref($this->data, $prelim);

        /** @var array<int,array{int,int}> $compressed */
        $compressed = [];
        $rootRef = null;
        $encrypt = null;
        $infoRef = null;
        $catalogNum = null;

        foreach (array_keys($offsets) as $num) {
            try {
                $obj = $tmp->getObject($num);
            } catch (PdfParseException) {
                continue;
            }

            if ($obj instanceof PdfStream) {
                $type = $this->typeName($obj->dict);
                if ($type === 'ObjStm') {
                    $this->indexObjectStream($tmp, $obj, $num, $compressed);
                } elseif ($type === 'XRef') {
                    $rootRef ??= $this->refOrNull($obj->dict->get('Root'));
                    $encrypt ??= $obj->dict->get('Encrypt');
                    $infoRef ??= $obj->dict->get('Info');
                }
            } elseif ($obj instanceof PdfDictionary) {
                if ($catalogNum === null && $this->typeName($obj) === 'Catalog') {
                    $catalogNum = $num;
                }
            }
        }

        $trailer = $this->buildTrailer($rootRef, $catalogNum, $encrypt, $infoRef);

        return new XrefTable($offsets, $gens, $trailer, $compressed);
    }

    /**
     * @return array{0: array<int,int>, 1: array<int,int>}
     */
    private function scanObjects(): array
    {
        $offsets = [];
        $gens = [];
        if (preg_match_all('/\b(\d{1,10})\s+(\d{1,5})\s+obj\b/', $this->data, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[0] as $i => $whole) {
                $num = (int) $m[1][$i][0];
                $offsets[$num] = $whole[1]; // last occurrence wins (newest)
                $gens[$num] = (int) $m[2][$i][0];
            }
        }
        return [$offsets, $gens];
    }

    /**
     * @param array<int,array{int,int}> $compressed
     */
    private function indexObjectStream(
        ReaderDocument $doc,
        PdfStream $stream,
        int $streamNumber,
        array &$compressed,
    ): void {
        try {
            $data = $doc->streamData($stream);
        } catch (PdfParseException) {
            return;
        }
        $n = $stream->dict->get('N');
        if (!is_int($n)) {
            return;
        }
        $lexer = new Lexer($data, 0);
        for ($i = 0; $i < $n; $i++) {
            $numTok = $lexer->nextToken();
            $offTok = $lexer->nextToken();
            if ($numTok->type !== TokenType::Number || $offTok->type !== TokenType::Number) {
                break;
            }
            $objNum = (int) $numTok->value;
            // Only fill gaps: a top-level definition always outranks a
            // compressed copy discovered here.
            if (!array_key_exists($objNum, $compressed)) {
                $compressed[$objNum] = [$streamNumber, $i];
            }
        }
    }

    private function buildTrailer(
        ?PdfReference $rootRef,
        ?int $catalogNum,
        mixed $encrypt,
        mixed $infoRef,
    ): PdfDictionary {
        // Prefer a real classic trailer if the file still has one with /Root.
        $classic = $this->findClassicTrailer();
        if ($classic !== null) {
            return $classic;
        }

        $items = [];
        if ($rootRef !== null) {
            $items['Root'] = $rootRef;
        } elseif ($catalogNum !== null) {
            $items['Root'] = new PdfReference($catalogNum, 0);
        } else {
            throw new PdfParseException('Recovery failed: no document catalog found');
        }
        if ($encrypt !== null && !$encrypt instanceof PdfNull) {
            $items['Encrypt'] = $encrypt;
        }
        if ($infoRef !== null && !$infoRef instanceof PdfNull) {
            $items['Info'] = $infoRef;
        }
        return new PdfDictionary($items);
    }

    private function findClassicTrailer(): ?PdfDictionary
    {
        $pos = strrpos($this->data, 'trailer');
        while ($pos !== false) {
            try {
                $value = (new ObjectParser(new Lexer($this->data, $pos + strlen('trailer'))))->parseValue();
                if ($value instanceof PdfDictionary && $value->has('Root')) {
                    return $value;
                }
            } catch (PdfParseException) {
                // Try an earlier 'trailer' occurrence.
            }
            $pos = $pos > 0 ? strrpos(substr($this->data, 0, $pos), 'trailer') : false;
        }
        return null;
    }

    private function typeName(PdfDictionary $dict): ?string
    {
        $type = $dict->get('Type');
        return $type instanceof PdfName ? $type->value : null;
    }

    private function refOrNull(mixed $value): ?PdfReference
    {
        return $value instanceof PdfReference ? $value : null;
    }
}
