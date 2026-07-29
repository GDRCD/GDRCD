<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * Reads the full cross-reference chain — classic tables (§7.5.4), cross-
 * reference streams (§7.5.8), and hybrid `/XRefStm` files — following `/Prev`
 * across incremental updates.
 *
 * Precedence is newest-first: the first definition seen for an object number
 * wins, and *free* entries also claim the number so an older section cannot
 * resurrect a deleted object.
 */
final class XrefReader
{
    public function __construct(private readonly string $data)
    {
    }

    public function read(): XrefTable
    {
        /** @var array<int,int> $offsets */
        $offsets = [];
        /** @var array<int,int> $gens */
        $gens = [];
        /** @var array<int,array{int,int}> $compressed */
        $compressed = [];
        /** @var array<int,true> $claimed object numbers already decided (incl. free) */
        $claimed = [];
        $trailer = null;

        $pending = [$this->findStartXref()];
        $seenOffset = [];

        while ($pending !== []) {
            $offset = array_shift($pending);
            if (isset($seenOffset[$offset]) || $offset < 0 || $offset >= strlen($this->data)) {
                continue;
            }
            $seenOffset[$offset] = true;

            $section = $this->readSection($offset);

            foreach ($section['type1'] as $num => [$off, $gen]) {
                if (!isset($claimed[$num])) {
                    $claimed[$num] = true;
                    $offsets[$num] = $off;
                    $gens[$num] = $gen;
                }
            }
            foreach ($section['type2'] as $num => $loc) {
                if (!isset($claimed[$num])) {
                    $claimed[$num] = true;
                    $compressed[$num] = $loc;
                }
            }
            foreach ($section['free'] as $num) {
                $claimed[$num] ??= true;
            }

            $dict = $section['dict'];
            $trailer ??= $dict;

            // Hybrid /XRefStm outranks older /Prev; enqueue in that order.
            $xrefStm = $dict->get('XRefStm');
            if (is_int($xrefStm)) {
                $pending[] = $xrefStm;
            }
            $prev = $dict->get('Prev');
            if (is_int($prev)) {
                $pending[] = $prev;
            }
        }

        if ($trailer === null) {
            throw new PdfParseException('No trailer found');
        }

        return new XrefTable($offsets, $gens, $trailer, $compressed);
    }

    private function findStartXref(): int
    {
        $pos = strrpos($this->data, 'startxref');
        if ($pos === false) {
            throw new PdfParseException('No startxref marker found');
        }
        $tok = (new Lexer($this->data, $pos + strlen('startxref')))->nextToken();
        if ($tok->type !== TokenType::Number || !is_int($tok->value)) {
            throw new PdfParseException('Malformed startxref offset');
        }
        return $tok->value;
    }

    /**
     * @return array{
     *   type1: array<int,array{int,int}>,
     *   type2: array<int,array{int,int}>,
     *   free: list<int>,
     *   dict: PdfDictionary
     * }
     */
    private function readSection(int $offset): array
    {
        $lexer = new Lexer($this->data, $offset);
        $head = $lexer->nextToken();

        if ($head->isKeyword('xref')) {
            return $this->readClassicSection($lexer);
        }
        // Otherwise it must be an `N G obj` cross-reference stream.
        return $this->readStreamSection($offset);
    }

    /**
     * @return array{type1: array<int,array{int,int}>, type2: array<int,array{int,int}>, free: list<int>, dict: PdfDictionary}
     */
    private function readClassicSection(Lexer $lexer): array
    {
        $type1 = [];
        $free = [];

        while (true) {
            $tok = $lexer->nextToken();
            if ($tok->isKeyword('trailer')) {
                break;
            }
            if ($tok->type !== TokenType::Number || !is_int($tok->value)) {
                throw new PdfParseException('Malformed xref subsection header');
            }
            $start = $tok->value;
            $countTok = $lexer->nextToken();
            if ($countTok->type !== TokenType::Number || !is_int($countTok->value)) {
                throw new PdfParseException('Malformed xref subsection count');
            }
            for ($i = 0; $i < $countTok->value; $i++) {
                $offTok = $lexer->nextToken();
                $genTok = $lexer->nextToken();
                $typeTok = $lexer->nextToken();
                if ($offTok->type !== TokenType::Number
                    || $genTok->type !== TokenType::Number
                    || $typeTok->type !== TokenType::Keyword
                ) {
                    throw new PdfParseException('Malformed xref entry');
                }
                $num = $start + $i;
                if ($typeTok->value === 'n') {
                    $type1[$num] = [(int) $offTok->value, (int) $genTok->value];
                } else {
                    $free[] = $num;
                }
            }
        }

        $trailer = (new ObjectParser($lexer))->parseValue();
        if (!$trailer instanceof PdfDictionary) {
            throw new PdfParseException('Trailer is not a dictionary');
        }

        return ['type1' => $type1, 'type2' => [], 'free' => $free, 'dict' => $trailer];
    }

    /**
     * @return array{type1: array<int,array{int,int}>, type2: array<int,array{int,int}>, free: list<int>, dict: PdfDictionary}
     */
    private function readStreamSection(int $offset): array
    {
        $obj = (new ObjectParser(new Lexer($this->data, $offset)))->parseIndirectObject();
        $stream = $obj['value'];
        if (!$stream instanceof PdfStream) {
            throw new PdfParseException("Expected xref stream at offset {$offset}");
        }
        $dict = $stream->dict;

        $w = $this->intList($dict->get('W'));
        if (count($w) !== 3) {
            throw new PdfParseException('Xref stream /W must have 3 entries');
        }
        [$w0, $w1, $w2] = $w;
        $recLen = $w0 + $w1 + $w2;
        if ($recLen === 0) {
            throw new PdfParseException('Xref stream /W widths are all zero');
        }

        $data = $this->decodeXrefStream($stream);

        $index = $this->intList($dict->get('Index'));
        if ($index === []) {
            $size = $dict->get('Size');
            $index = [0, is_int($size) ? $size : intdiv(strlen($data), $recLen)];
        }

        $type1 = [];
        $type2 = [];
        $free = [];
        $p = 0;
        $len = strlen($data);

        for ($k = 0; $k + 1 < count($index); $k += 2) {
            $start = $index[$k];
            $count = $index[$k + 1];
            for ($j = 0; $j < $count && $p + $recLen <= $len; $j++) {
                $type = $w0 === 0 ? 1 : $this->readBE($data, $p, $w0);
                $f2 = $this->readBE($data, $p + $w0, $w1);
                $f3 = $this->readBE($data, $p + $w0 + $w1, $w2);
                $p += $recLen;
                $num = $start + $j;
                match ($type) {
                    0 => $free[] = $num,
                    1 => $type1[$num] = [$f2, $f3],
                    2 => $type2[$num] = [$f2, $f3],
                    default => null, // unknown types are reserved; ignore
                };
            }
        }

        return ['type1' => $type1, 'type2' => $type2, 'free' => $free, 'dict' => $dict];
    }

    /**
     * Decode an xref stream. Its /Filter and /DecodeParms are required to be
     * direct objects (they bootstrap the file), so no resolver is needed.
     */
    private function decodeXrefStream(PdfStream $stream): string
    {
        $dict = $stream->dict;
        $filter = $dict->get('Filter');
        $name = $filter instanceof PdfName ? $filter->value : null;

        $data = $stream->raw;
        if ($name === 'FlateDecode' || $name === 'Fl') {
            $data = Filters::flate($data);
        } elseif ($name !== null) {
            throw new PdfParseException("Unsupported xref-stream filter: {$name}");
        }

        $parms = $dict->get('DecodeParms');
        if ($parms instanceof PdfDictionary) {
            $predictor = $parms->get('Predictor');
            if (is_int($predictor) && $predictor > 1) {
                $data = Filters::applyPredictor(
                    $data,
                    $predictor,
                    is_int($parms->get('Colors')) ? $parms->get('Colors') : 1,
                    is_int($parms->get('BitsPerComponent')) ? $parms->get('BitsPerComponent') : 8,
                    is_int($parms->get('Columns')) ? $parms->get('Columns') : 1,
                );
            }
        }

        return $data;
    }

    /** @return list<int> */
    private function intList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $out = [];
        foreach ($value as $v) {
            if (is_int($v)) {
                $out[] = $v;
            }
        }
        return $out;
    }

    private function readBE(string $data, int $pos, int $width): int
    {
        $n = 0;
        for ($i = 0; $i < $width; $i++) {
            $n = ($n << 8) | ord($data[$pos + $i]);
        }
        return $n;
    }
}
