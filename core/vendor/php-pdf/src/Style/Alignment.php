<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Style;

/**
 * Horizontal alignment for paragraphs and table cells.
 *
 * Value names follow OOXML w:jc convention. HTML mapping for LTR scripts:
 *  - Start      → left
 *  - End        → right
 *  - Both       → justify (regular lines)
 *  - Distribute → justify including the last line
 */
enum Alignment: string
{
    case Start = 'start';
    case Center = 'center';
    case End = 'end';
    case Both = 'both';
    case Distribute = 'distribute';
}
