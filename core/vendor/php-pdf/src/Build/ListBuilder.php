<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Build;

use Closure;
use Dskripchenko\PhpPdf\Element\ListItem;
use Dskripchenko\PhpPdf\Element\ListNode;
use Dskripchenko\PhpPdf\Style\ListFormat;

/**
 * Fluent builder for ListNode (bullet / ordered).
 *
 * Pattern:
 *   ListBuilder::bullet()
 *       ->item('First')
 *       ->item('Second', fn($i) => $i->orderedList(fn($l) => $l
 *           ->item('Nested 2.1')
 *           ->item('Nested 2.2')
 *       ))
 *       ->build();
 */
final class ListBuilder
{
    /** @var list<ListItem> */
    private array $items = [];

    private int $startAt = 1;

    private float $spaceBeforePt = 0;

    private float $spaceAfterPt = 6;

    public function __construct(
        private ListFormat $format = ListFormat::Bullet,
    ) {}

    public static function bullet(): self
    {
        return new self(ListFormat::Bullet);
    }

    public static function ordered(ListFormat $format = ListFormat::Decimal): self
    {
        return new self($format);
    }

    public function format(ListFormat $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function startAt(int $n): self
    {
        $this->startAt = $n;

        return $this;
    }

    public function spaceBefore(float $pt): self
    {
        $this->spaceBeforePt = $pt;

        return $this;
    }

    public function spaceAfter(float $pt): self
    {
        $this->spaceAfterPt = $pt;

        return $this;
    }

    /**
     * Adds item. $content:
     *  - string         — single-paragraph item
     *  - Closure        — ListItemBuilder for configurability
     *  - ListItem       — a ready AST node
     *
     * Optional $nest closure — convenience for a nested list.
     */
    public function item(string|Closure|ListItem $content, ?Closure $nest = null): self
    {
        if ($content instanceof ListItem) {
            $this->items[] = $content;

            return $this;
        }
        $b = new ListItemBuilder;
        if (is_string($content)) {
            $b->text($content);
        } else {
            $content($b);
        }
        if ($nest !== null) {
            // Default nested type: same as parent.
            $b->nest($this->format, $nest);
        }
        $this->items[] = $b->build();

        return $this;
    }

    /**
     * @param  list<string>  $texts
     */
    public function items(array $texts): self
    {
        foreach ($texts as $text) {
            $this->item($text);
        }

        return $this;
    }

    public function build(): ListNode
    {
        return new ListNode(
            items: $this->items,
            format: $this->format,
            startAt: $this->startAt,
            spaceBeforePt: $this->spaceBeforePt,
            spaceAfterPt: $this->spaceAfterPt,
        );
    }
}
