<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Style;

/**
 * Four-sided border set for paragraphs, table cells, or tables.
 *
 * Each side is optional; null means no border on that side. All four
 * sides can be styled independently.
 */
final readonly class BorderSet
{
    public function __construct(
        public ?Border $top = null,
        public ?Border $left = null,
        public ?Border $bottom = null,
        public ?Border $right = null,
    ) {}

    /**
     * Convenience: apply the same border to all four sides.
     */
    public static function all(Border $border): self
    {
        return new self($border, $border, $border, $border);
    }

    public function hasAnyBorder(): bool
    {
        return $this->top !== null
            || $this->left !== null
            || $this->bottom !== null
            || $this->right !== null;
    }
}
