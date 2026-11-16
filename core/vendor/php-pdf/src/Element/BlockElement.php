<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

/**
 * Marker interface for block-level AST elements (Paragraph, Heading,
 * Table, ListNode, Image, HorizontalRule, PageBreak, etc.).
 *
 * Block elements live in `Section.body`, `Cell.children`, and other
 * block sequences. They flow vertically, one after another.
 */
interface BlockElement
{
}
