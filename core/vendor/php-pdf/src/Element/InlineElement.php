<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

/**
 * Marker interface for inline-level AST elements (Run, LineBreak,
 * Hyperlink, Bookmark, Image inline, Field, Footnote).
 *
 * Inline elements live in `Paragraph.children` and flow horizontally,
 * wrapping to the next line when they exceed the line width.
 */
interface InlineElement
{
}
