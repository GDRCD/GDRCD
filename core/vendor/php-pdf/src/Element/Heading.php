<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\ParagraphStyle;

/**
 * Semantic heading element with level 1-6.
 *
 * Rendered as a paragraph with auto-styled bold + larger font:
 *   H1=24pt, H2=20pt, H3=16pt, H4=14pt, H5=12pt, H6=11pt
 *
 * In Tagged PDF mode emits as /H1.../H6 struct elements so accessibility
 * readers can navigate the heading hierarchy. With an explicit `$anchor`
 * a named destination is registered at the heading position, addressable
 * via internal hyperlinks (`<a href="#anchor">`).
 */
final readonly class Heading implements BlockElement
{
    /**
     * @param  list<InlineElement>  $children
     * @param  string|null  $anchor  Optional named destination (without `#`
     *                                prefix). When set, the engine registers
     *                                the heading position as a named target
     *                                for internal hyperlinks.
     */
    public function __construct(
        public int $level,
        public array $children,
        public ?ParagraphStyle $style = null,
        public ?string $anchor = null,
    ) {
        if ($level < 1 || $level > 6) {
            throw new \InvalidArgumentException('Heading level must be 1..6');
        }
    }

    /**
     * Derive a URL-safe slug from the heading text content. Lowercase,
     * non-alphanumeric characters collapsed to dashes, trimmed.
     */
    public function autoAnchor(): string
    {
        $text = '';
        foreach ($this->children as $child) {
            if ($child instanceof \Dskripchenko\PhpPdf\Element\Run) {
                $text .= $child->text.' ';
            }
        }
        $slug = trim($text);
        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return mb_strtolower($slug, 'UTF-8');
    }

    public function defaultFontSizePt(): float
    {
        return match ($this->level) {
            1 => 24.0,
            2 => 20.0,
            3 => 16.0,
            4 => 14.0,
            5 => 12.0,
            6 => 11.0,
            default => 11.0,
        };
    }
}
