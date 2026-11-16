<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\RunStyle;

/**
 * Inline span of text with a single style.
 *
 * Mid-paragraph style changes happen by splitting into multiple runs:
 * `[Run('foo', plain), Run('bar', bold)]`. Text is UTF-8 — the layout
 * engine maps it to glyph IDs through PdfFont.
 */
final readonly class Run implements InlineElement
{
    public function __construct(
        public string $text,
        public RunStyle $style = new RunStyle,
    ) {}
}
