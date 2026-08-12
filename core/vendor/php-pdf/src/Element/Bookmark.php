<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

/**
 * Named destination — target for internal hyperlinks.
 *
 * The layout engine registers a destination in Catalog.Dests at the
 * current cursor position when this element is rendered. Names must be
 * unique within a document. May wrap inline children (the link target
 * text) or be empty (just a position marker in the stream).
 */
final readonly class Bookmark implements InlineElement
{
    /**
     * @param  list<InlineElement>  $children
     */
    public function __construct(
        public string $name,
        public array $children = [],
    ) {}
}
