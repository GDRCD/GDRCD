<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * Byte-level tokenizer for PDF object syntax (ISO 32000-1 §7.2, §7.3).
 *
 * The lexer is deliberately stateless beyond a cursor: it never resolves
 * indirect references and never decodes stream bodies. Callers that need to
 * read raw stream bytes use {@see position()} / {@see seek()} to move the
 * cursor directly over binary regions the token grammar does not cover.
 */
final class Lexer
{
    private const WHITESPACE = "\x00\x09\x0A\x0C\x0D\x20";
    private const DELIMITERS = "()<>[]{}/%";

    private int $pos;
    private readonly int $len;

    public function __construct(
        private readonly string $data,
        int $pos = 0,
    ) {
        $this->pos = $pos;
        $this->len = strlen($data);
    }

    public function position(): int
    {
        return $this->pos;
    }

    public function seek(int $pos): void
    {
        $this->pos = $pos;
    }

    public function data(): string
    {
        return $this->data;
    }

    /**
     * Read and return the next token, advancing the cursor past it.
     */
    public function nextToken(): Token
    {
        $this->skipWhitespaceAndComments();

        if ($this->pos >= $this->len) {
            return new Token(TokenType::Eof, null, $this->pos);
        }

        $start = $this->pos;
        $c = $this->data[$this->pos];

        switch ($c) {
            case '<':
                if ($this->pos + 1 < $this->len && $this->data[$this->pos + 1] === '<') {
                    $this->pos += 2;
                    return new Token(TokenType::DictOpen, '<<', $start);
                }
                return $this->readHexString($start);

            case '>':
                if ($this->pos + 1 < $this->len && $this->data[$this->pos + 1] === '>') {
                    $this->pos += 2;
                    return new Token(TokenType::DictClose, '>>', $start);
                }
                throw new PdfParseException("Unexpected '>' at offset {$start}");

            case '[':
                $this->pos++;
                return new Token(TokenType::ArrayOpen, '[', $start);

            case ']':
                $this->pos++;
                return new Token(TokenType::ArrayClose, ']', $start);

            case '{':
                $this->pos++;
                return new Token(TokenType::BraceOpen, '{', $start);

            case '}':
                $this->pos++;
                return new Token(TokenType::BraceClose, '}', $start);

            case '(':
                return $this->readLiteralString($start);

            case '/':
                return $this->readName($start);
        }

        if ($c === '+' || $c === '-' || $c === '.' || ($c >= '0' && $c <= '9')) {
            return $this->readNumber($start);
        }

        return $this->readKeyword($start);
    }

    private function skipWhitespaceAndComments(): void
    {
        while ($this->pos < $this->len) {
            $c = $this->data[$this->pos];
            if (strpos(self::WHITESPACE, $c) !== false) {
                $this->pos++;
                continue;
            }
            if ($c === '%') {
                // Comment: skip to end of line.
                while ($this->pos < $this->len
                    && $this->data[$this->pos] !== "\r"
                    && $this->data[$this->pos] !== "\n"
                ) {
                    $this->pos++;
                }
                continue;
            }
            break;
        }
    }

    private function readNumber(int $start): Token
    {
        $s = $this->pos;
        if ($this->data[$this->pos] === '+' || $this->data[$this->pos] === '-') {
            $this->pos++;
        }
        $isReal = false;
        while ($this->pos < $this->len) {
            $c = $this->data[$this->pos];
            if ($c >= '0' && $c <= '9') {
                $this->pos++;
            } elseif ($c === '.') {
                $isReal = true;
                $this->pos++;
            } else {
                break;
            }
        }
        $lexeme = substr($this->data, $s, $this->pos - $s);
        $value = $isReal ? (float) $lexeme : (int) $lexeme;
        return new Token(TokenType::Number, $value, $start);
    }

    private function readName(int $start): Token
    {
        $this->pos++; // consume '/'
        $out = '';
        while ($this->pos < $this->len) {
            $c = $this->data[$this->pos];
            if (strpos(self::WHITESPACE, $c) !== false || strpos(self::DELIMITERS, $c) !== false) {
                break;
            }
            if ($c === '#' && $this->pos + 2 < $this->len) {
                $hex = substr($this->data, $this->pos + 1, 2);
                if (ctype_xdigit($hex)) {
                    $out .= chr((int) hexdec($hex));
                    $this->pos += 3;
                    continue;
                }
            }
            $out .= $c;
            $this->pos++;
        }
        return new Token(TokenType::Name, $out, $start);
    }

    private function readLiteralString(int $start): Token
    {
        $this->pos++; // consume '('
        $out = '';
        $depth = 1;
        while ($this->pos < $this->len) {
            $c = $this->data[$this->pos];
            if ($c === '\\') {
                $this->pos++;
                if ($this->pos >= $this->len) {
                    break;
                }
                $e = $this->data[$this->pos];
                switch ($e) {
                    case 'n': $out .= "\n"; $this->pos++; break;
                    case 'r': $out .= "\r"; $this->pos++; break;
                    case 't': $out .= "\t"; $this->pos++; break;
                    case 'b': $out .= "\x08"; $this->pos++; break;
                    case 'f': $out .= "\x0C"; $this->pos++; break;
                    case '(': $out .= '('; $this->pos++; break;
                    case ')': $out .= ')'; $this->pos++; break;
                    case '\\': $out .= '\\'; $this->pos++; break;
                    case "\r":
                        // Line continuation: backslash + EOL is elided.
                        $this->pos++;
                        if ($this->pos < $this->len && $this->data[$this->pos] === "\n") {
                            $this->pos++;
                        }
                        break;
                    case "\n":
                        $this->pos++;
                        break;
                    default:
                        if ($e >= '0' && $e <= '7') {
                            $oct = '';
                            for ($i = 0; $i < 3 && $this->pos < $this->len; $i++) {
                                $d = $this->data[$this->pos];
                                if ($d < '0' || $d > '7') {
                                    break;
                                }
                                $oct .= $d;
                                $this->pos++;
                            }
                            $out .= chr(((int) octdec($oct)) & 0xFF);
                        } else {
                            // Unknown escape: backslash dropped, char kept.
                            $out .= $e;
                            $this->pos++;
                        }
                }
                continue;
            }
            if ($c === '(') {
                $depth++;
                $out .= $c;
                $this->pos++;
                continue;
            }
            if ($c === ')') {
                $depth--;
                $this->pos++;
                if ($depth === 0) {
                    return new Token(TokenType::Str, $out, $start);
                }
                $out .= $c;
                continue;
            }
            $out .= $c;
            $this->pos++;
        }
        throw new PdfParseException("Unterminated literal string at offset {$start}");
    }

    private function readHexString(int $start): Token
    {
        $this->pos++; // consume '<'
        $hex = '';
        while ($this->pos < $this->len) {
            $c = $this->data[$this->pos];
            if ($c === '>') {
                $this->pos++;
                if (strlen($hex) % 2 === 1) {
                    $hex .= '0';
                }
                return new Token(TokenType::HexStr, hex2bin($hex), $start);
            }
            if (ctype_xdigit($c)) {
                $hex .= $c;
            } elseif (strpos(self::WHITESPACE, $c) === false) {
                throw new PdfParseException("Invalid hex digit '{$c}' at offset {$this->pos}");
            }
            $this->pos++;
        }
        throw new PdfParseException("Unterminated hex string at offset {$start}");
    }

    private function readKeyword(int $start): Token
    {
        $s = $this->pos;
        while ($this->pos < $this->len) {
            $c = $this->data[$this->pos];
            if (strpos(self::WHITESPACE, $c) !== false || strpos(self::DELIMITERS, $c) !== false) {
                break;
            }
            $this->pos++;
        }
        if ($this->pos === $s) {
            // Not a valid regular character — consume it to guarantee progress.
            $this->pos++;
        }
        return new Token(TokenType::Keyword, substr($this->data, $s, $this->pos - $s), $start);
    }
}
