<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Style;

/**
 * List marker format.
 */
enum ListFormat: string
{
    case Bullet = 'bullet';
    case Decimal = 'decimal';            // 1, 2, 3
    case LowerLetter = 'lowerLetter';    // a, b, c
    case UpperLetter = 'upperLetter';    // A, B, C
    case LowerRoman = 'lowerRoman';      // i, ii, iii
    case UpperRoman = 'upperRoman';      // I, II, III

    public function isOrdered(): bool
    {
        return $this !== self::Bullet;
    }
}
