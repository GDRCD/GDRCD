<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * A single lexical token: its {@see TokenType}, decoded value (when relevant),
 * and the byte offset in the source where it began.
 */
final readonly class Token
{
    public function __construct(
        public TokenType $type,
        public mixed $value,
        public int $offset,
    ) {
    }

    public function isKeyword(string $word): bool
    {
        return $this->type === TokenType::Keyword && $this->value === $word;
    }
}
