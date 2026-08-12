<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\ListFormat;

/**
 * Block-level list (bullet or ordered).
 *
 * Nested lists are represented via `ListItem.nestedList`; the layout
 * engine recurses with increased indentation. Default format is Bullet
 * when `format` is null.
 */
final readonly class ListNode implements BlockElement
{
    /**
     * @param  list<ListItem>  $items
     */
    public function __construct(
        public array $items = [],
        public ?ListFormat $format = null,
        public int $startAt = 1,
        public float $spaceBeforePt = 0,
        public float $spaceAfterPt = 6,
    ) {}

    public function effectiveFormat(): ListFormat
    {
        return $this->format ?? ListFormat::Bullet;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }
}
