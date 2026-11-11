<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

/**
 * Forced page break.
 *
 * Implements both BlockElement and InlineElement: can appear standalone
 * at document top level or embedded in a paragraph (the current line
 * finishes and a new page starts).
 */
final readonly class PageBreak implements BlockElement, InlineElement
{
}
