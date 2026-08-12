<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

/**
 * Single list entry. `$children` holds the item body (usually paragraphs,
 * sometimes images or nested tables). `$nestedList`, when present, renders
 * after `$children` at the next indent level. ListItem is not a
 * BlockElement — it only lives inside ListNode.
 */
final readonly class ListItem
{
    /**
     * @param  list<BlockElement>  $children
     */
    public function __construct(
        public array $children,
        public ?ListNode $nestedList = null,
    ) {}
}
