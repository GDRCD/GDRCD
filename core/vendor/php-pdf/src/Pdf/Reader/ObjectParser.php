<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * Recursive-descent parser over the {@see Lexer} token stream, building the
 * PDF object model (ISO 32000-1 §7.3).
 *
 * Produced value types:
 *   - int / float        numbers
 *   - bool               true / false
 *   - {@see PdfNull}     null
 *   - {@see PdfName}     /names
 *   - {@see PdfString}   (literal) and <hex> strings
 *   - {@see PdfReference} indirect references `N G R`
 *   - {@see PdfDictionary} << ... >>
 *   - {@see PdfStream}   dictionary + raw body
 *   - array (list)       [ ... ]
 *
 * Indirect `/Length` on streams cannot be resolved without the object table,
 * so this phase falls back to scanning for `endstream` when the length is not
 * a direct integer. The resolver (Phase P2) supplies exact lengths later.
 */
final class ObjectParser
{
    public function __construct(private readonly Lexer $lexer)
    {
    }

    public function lexer(): Lexer
    {
        return $this->lexer;
    }

    /**
     * Parse one indirect object `N G obj <value> endobj`.
     *
     * @return array{number:int, generation:int, value:mixed}
     */
    public function parseIndirectObject(): array
    {
        $numTok = $this->lexer->nextToken();
        $genTok = $this->lexer->nextToken();
        $objTok = $this->lexer->nextToken();

        if ($numTok->type !== TokenType::Number || $genTok->type !== TokenType::Number
            || !$objTok->isKeyword('obj')
        ) {
            throw new PdfParseException("Expected 'N G obj' header at offset {$numTok->offset}");
        }

        $value = $this->parseValue();

        // Consume the trailing 'endobj' (tolerate its absence at EOF).
        $save = $this->lexer->position();
        $end = $this->lexer->nextToken();
        if (!$end->isKeyword('endobj') && $end->type !== TokenType::Eof) {
            $this->lexer->seek($save);
        }

        return [
            'number' => (int) $numTok->value,
            'generation' => (int) $genTok->value,
            'value' => $value,
        ];
    }

    /**
     * Parse a single object value starting at the current cursor.
     */
    public function parseValue(): mixed
    {
        $tok = $this->lexer->nextToken();
        return $this->parseFromToken($tok);
    }

    private function parseFromToken(Token $tok): mixed
    {
        switch ($tok->type) {
            case TokenType::Number:
                return $this->maybeReference($tok);

            case TokenType::Name:
                return new PdfName((string) $tok->value);

            case TokenType::Str:
                return new PdfString((string) $tok->value, hex: false);

            case TokenType::HexStr:
                return new PdfString((string) $tok->value, hex: true);

            case TokenType::ArrayOpen:
                return $this->parseArray();

            case TokenType::DictOpen:
                return $this->parseDictionaryOrStream();

            case TokenType::Keyword:
                return match ($tok->value) {
                    'true' => true,
                    'false' => false,
                    'null' => PdfNull::instance(),
                    default => throw new PdfParseException(
                        "Unexpected keyword '{$tok->value}' at offset {$tok->offset}"
                    ),
                };

            case TokenType::Eof:
                throw new PdfParseException('Unexpected end of input while parsing value');

            default:
                throw new PdfParseException(
                    "Unexpected token {$tok->type->name} at offset {$tok->offset}"
                );
        }
    }

    /**
     * A number may begin an indirect reference `N G R`. Peek ahead and rewind
     * if the following tokens do not complete the reference form.
     */
    private function maybeReference(Token $numTok): int|float|PdfReference
    {
        if (!is_int($numTok->value)) {
            return $numTok->value;
        }

        $save = $this->lexer->position();
        $t2 = $this->lexer->nextToken();
        if ($t2->type === TokenType::Number && is_int($t2->value)) {
            $t3 = $this->lexer->nextToken();
            if ($t3->isKeyword('R')) {
                return new PdfReference($numTok->value, $t2->value);
            }
        }

        $this->lexer->seek($save);
        return $numTok->value;
    }

    /** @return list<mixed> */
    private function parseArray(): array
    {
        $out = [];
        while (true) {
            $tok = $this->lexer->nextToken();
            if ($tok->type === TokenType::ArrayClose) {
                return $out;
            }
            if ($tok->type === TokenType::Eof) {
                throw new PdfParseException('Unterminated array');
            }
            $out[] = $this->parseFromToken($tok);
        }
    }

    private function parseDictionaryOrStream(): PdfDictionary|PdfStream
    {
        $items = [];
        while (true) {
            $keyTok = $this->lexer->nextToken();
            if ($keyTok->type === TokenType::DictClose) {
                break;
            }
            if ($keyTok->type === TokenType::Eof) {
                throw new PdfParseException('Unterminated dictionary');
            }
            if ($keyTok->type !== TokenType::Name) {
                throw new PdfParseException(
                    "Dictionary key must be a name at offset {$keyTok->offset}"
                );
            }
            $items[(string) $keyTok->value] = $this->parseValue();
        }

        $dict = new PdfDictionary($items);

        // A `stream` keyword immediately after the dictionary promotes it to a
        // stream object; otherwise rewind and return the plain dictionary.
        $save = $this->lexer->position();
        $next = $this->lexer->nextToken();
        if ($next->isKeyword('stream')) {
            return $this->readStreamBody($dict, $next->offset);
        }
        $this->lexer->seek($save);
        return $dict;
    }

    private function readStreamBody(PdfDictionary $dict, int $streamKeywordOffset): PdfStream
    {
        $data = $this->lexer->data();
        // Data begins after the keyword and its EOL (CRLF or LF; §7.3.8.1).
        $p = $streamKeywordOffset + strlen('stream');
        if (($data[$p] ?? '') === "\r") {
            $p++;
        }
        if (($data[$p] ?? '') === "\n") {
            $p++;
        }

        $length = $dict->get('Length');
        $raw = null;

        if (is_int($length)) {
            $candidate = substr($data, $p, $length);
            // Trust the length only if 'endstream' follows (allowing one EOL).
            $after = $p + $length;
            $tail = substr($data, $after, 20);
            if (preg_match('/^\s*endstream/', $tail) === 1) {
                $raw = $candidate;
                $p = $after;
            }
        }

        if ($raw === null) {
            // Fallback: scan for the terminating 'endstream'.
            $end = strpos($data, 'endstream', $p);
            if ($end === false) {
                throw new PdfParseException(
                    "Unterminated stream at offset {$streamKeywordOffset}"
                );
            }
            $bodyEnd = $end;
            // Trim exactly one EOL that precedes the 'endstream' keyword.
            if ($bodyEnd > $p && $data[$bodyEnd - 1] === "\n") {
                $bodyEnd--;
                if ($bodyEnd > $p && $data[$bodyEnd - 1] === "\r") {
                    $bodyEnd--;
                }
            } elseif ($bodyEnd > $p && $data[$bodyEnd - 1] === "\r") {
                $bodyEnd--;
            }
            $raw = substr($data, $p, $bodyEnd - $p);
            $p = $end;
        }

        // Position the cursor just past 'endstream'.
        $endStreamPos = strpos($data, 'endstream', $p);
        $this->lexer->seek(($endStreamPos === false ? $p : $endStreamPos) + strlen('endstream'));

        return new PdfStream($dict, $raw);
    }
}
