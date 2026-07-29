<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Style;

/**
 * Page orientation.
 *
 * PDF MediaBox is always `[0 0 width height]`; landscape simply means
 * width > height. `applyTo()` swaps dimensions for a given paper size.
 */
enum Orientation
{
    case Portrait;
    case Landscape;

    /**
     * Resolve `(width, height)` in points for the given paper size and
     * this orientation.
     *
     * @return array{0: float, 1: float}
     */
    public function applyTo(PaperSize $paper): array
    {
        return $this === self::Portrait
            ? [$paper->widthPt(), $paper->heightPt()]
            : [$paper->heightPt(), $paper->widthPt()];
    }
}
