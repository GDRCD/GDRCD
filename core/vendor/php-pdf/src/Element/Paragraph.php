<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\ParagraphStyle;
use Dskripchenko\PhpPdf\Style\RunStyle;

/**
 * Block-level container for inline content (Run, Image, Hyperlink, etc.).
 *
 * `$defaultRunStyle` is inherited by children whose Run.style omits
 * fontFamily/sizePt — CSS-style inheritance.
 */
final readonly class Paragraph implements BlockElement
{
    /**
     * @param  list<InlineElement>  $children
     */
    public function __construct(
        public array $children = [],
        public ParagraphStyle $style = new ParagraphStyle,
        public ?int $headingLevel = null,
        public RunStyle $defaultRunStyle = new RunStyle,
    ) {}

    public function isEmpty(): bool
    {
        return $this->children === [];
    }
}
