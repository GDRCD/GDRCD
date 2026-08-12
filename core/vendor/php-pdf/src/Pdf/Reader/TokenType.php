<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * Lexical token categories produced by {@see Lexer}.
 */
enum TokenType
{
    case Number;      // 12, -4, 3.14, .5, 4.
    case Name;        // /Type   (value = decoded name, no slash)
    case Str;         // (literal) or <hex>   (value = decoded bytes)
    case HexStr;      // marker variant of Str for <...> syntax
    case DictOpen;    // <<
    case DictClose;   // >>
    case ArrayOpen;   // [
    case ArrayClose;  // ]
    case BraceOpen;   // {   (PostScript / Type 4 functions)
    case BraceClose;  // }
    case Keyword;     // obj endobj stream endstream R true false null xref ...
    case Eof;
}
