<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

/**
 * Hyperlink wrapping inline content — either an external URL or an
 * internal named destination.
 *
 * Use `external()` for URLs, `internal()` for in-document anchors.
 * The layout engine attaches a /Link annotation covering the rendered
 * bounding box of the children.
 */
final readonly class Hyperlink implements InlineElement
{
    /**
     * @param  list<InlineElement>  $children
     */
    public function __construct(
        public array $children,
        public ?string $href = null,
        public ?string $anchor = null,
    ) {}

    public function isInternal(): bool
    {
        return $this->anchor !== null;
    }

    /**
     * @param  list<InlineElement>  $children
     */
    public static function external(string $href, array $children): self
    {
        return new self($children, href: $href);
    }

    /**
     * @param  list<InlineElement>  $children
     */
    public static function internal(string $anchor, array $children): self
    {
        return new self($children, anchor: $anchor);
    }
}
