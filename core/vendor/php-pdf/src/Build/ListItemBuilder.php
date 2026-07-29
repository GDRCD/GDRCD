<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Build;

use Closure;
use Dskripchenko\PhpPdf\Element\BlockElement;
use Dskripchenko\PhpPdf\Element\ListItem;
use Dskripchenko\PhpPdf\Element\ListNode;
use Dskripchenko\PhpPdf\Element\Paragraph;
use Dskripchenko\PhpPdf\Element\Run;
use Dskripchenko\PhpPdf\Style\ListFormat;

/**
 * Fluent builder for ListItem.
 *
 * Contains children + optional nestedList. The common pattern:
 *   $r->item('Top item')
 *     ->bulletList(fn($l) => $l->item('Sub 1')->item('Sub 2'))
 *
 * Or via the nest() callback:
 *   $r->item(fn($i) => $i
 *       ->text('Item text')
 *       ->nest(ListFormat::Decimal, fn($sub) => $sub->item('1.1'))
 *   )
 */
final class ListItemBuilder
{
    /** @var list<BlockElement> */
    private array $children = [];

    private ?ListNode $nestedList = null;

    public static function new(): self
    {
        return new self;
    }

    public function text(string $text): self
    {
        $this->children[] = new Paragraph([new Run($text)]);

        return $this;
    }

    public function paragraph(string|Closure|Paragraph $content): self
    {
        if ($content instanceof Paragraph) {
            $this->children[] = $content;

            return $this;
        }
        if (is_string($content)) {
            $this->children[] = new Paragraph([new Run($content)]);

            return $this;
        }
        $p = new ParagraphBuilder;
        $content($p);
        $this->children[] = $p->build();

        return $this;
    }

    public function block(BlockElement $element): self
    {
        $this->children[] = $element;

        return $this;
    }

    public function nest(ListFormat $format, Closure $build): self
    {
        $b = new ListBuilder($format);
        $build($b);
        $this->nestedList = $b->build();

        return $this;
    }

    public function bulletList(Closure $build): self
    {
        return $this->nest(ListFormat::Bullet, $build);
    }

    public function orderedList(Closure $build, ListFormat $format = ListFormat::Decimal): self
    {
        return $this->nest($format, $build);
    }

    public function build(): ListItem
    {
        if ($this->children === []) {
            $this->children = [new Paragraph([new Run('')])];
        }

        return new ListItem(children: $this->children, nestedList: $this->nestedList);
    }
}
