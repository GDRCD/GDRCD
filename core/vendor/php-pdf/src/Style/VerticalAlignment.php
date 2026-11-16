<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Style;

/**
 * Vertical alignment of cell content within its bounding box.
 */
enum VerticalAlignment: string
{
    case Top = 'top';
    case Center = 'center';
    case Bottom = 'bottom';
}
