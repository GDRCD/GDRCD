<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Style;

/**
 * Border line style — matches OOXML + CSS border-style.
 */
enum BorderStyle: string
{
    case None = 'none';
    case Single = 'single';      // CSS solid
    case Dashed = 'dashed';
    case Dotted = 'dotted';
    case Double = 'double';
}
