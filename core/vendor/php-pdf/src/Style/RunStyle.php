<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Style;

/**
 * Inline text style — attributes applied to a contiguous Run.
 *
 * All non-boolean fields are nullable; a null value means "inherit from
 * the enclosing scope" (paragraph default, then document default).
 * Boolean flags default to false.
 *
 * Colors are stored as RGB hex strings without `#` prefix, lowercase
 * (e.g. `'ff0000'`). Sizes are in PDF points (1pt = 1/72 inch).
 * `fontFamily` is resolved by the layout engine through a FontProvider.
 */
final readonly class RunStyle
{
    public function __construct(
        public ?float $sizePt = null,
        public ?string $color = null,
        public ?string $backgroundColor = null,
        public ?string $fontFamily = null,
        public bool $bold = false,
        public bool $italic = false,
        public bool $underline = false,
        public bool $strikethrough = false,
        public bool $superscript = false,
        public bool $subscript = false,
        /** Named highlight color (yellow, green, cyan, ...) — OOXML convention. */
        public ?string $highlight = null,
        /** Letter spacing in points, applied via PDF Tc operator. */
        public ?float $letterSpacingPt = null,
    ) {}

    public function withBold(bool $value = true): self
    {
        return $this->copy(bold: $value);
    }

    public function withItalic(bool $value = true): self
    {
        return $this->copy(italic: $value);
    }

    public function withUnderline(bool $value = true): self
    {
        return $this->copy(underline: $value);
    }

    public function withStrikethrough(bool $value = true): self
    {
        return $this->copy(strikethrough: $value);
    }

    public function withSuperscript(bool $value = true): self
    {
        return $this->copy(superscript: $value, subscript: false);
    }

    public function withSubscript(bool $value = true): self
    {
        return $this->copy(subscript: $value, superscript: false);
    }

    public function withFontFamily(string $family): self
    {
        return $this->copy(fontFamily: $family);
    }

    public function withSizePt(float $sizePt): self
    {
        return $this->copy(sizePt: $sizePt);
    }

    public function withColor(string $hex): self
    {
        return $this->copy(color: strtolower(ltrim($hex, '#')));
    }

    public function withHighlight(string $highlight): self
    {
        return $this->copy(highlight: $highlight);
    }

    public function withLetterSpacingPt(float $pt): self
    {
        return $this->copy(letterSpacingPt: $pt);
    }

    /**
     * Cascade resolution — fill null fields from `$parent`. Boolean flags
     * OR with parent (so a child's `bold: true` always wins).
     */
    public function inheritFrom(self $parent): self
    {
        return new self(
            sizePt: $this->sizePt ?? $parent->sizePt,
            color: $this->color ?? $parent->color,
            backgroundColor: $this->backgroundColor ?? $parent->backgroundColor,
            fontFamily: $this->fontFamily ?? $parent->fontFamily,
            bold: $this->bold || $parent->bold,
            italic: $this->italic || $parent->italic,
            underline: $this->underline || $parent->underline,
            strikethrough: $this->strikethrough || $parent->strikethrough,
            superscript: $this->superscript || $parent->superscript,
            subscript: $this->subscript || $parent->subscript,
            highlight: $this->highlight ?? $parent->highlight,
            letterSpacingPt: $this->letterSpacingPt ?? $parent->letterSpacingPt,
        );
    }

    private function copy(
        ?float $sizePt = null,
        ?string $color = null,
        ?string $backgroundColor = null,
        ?string $fontFamily = null,
        ?bool $bold = null,
        ?bool $italic = null,
        ?bool $underline = null,
        ?bool $strikethrough = null,
        ?bool $superscript = null,
        ?bool $subscript = null,
        ?string $highlight = null,
        ?float $letterSpacingPt = null,
    ): self {
        return new self(
            sizePt: $sizePt ?? $this->sizePt,
            color: $color ?? $this->color,
            backgroundColor: $backgroundColor ?? $this->backgroundColor,
            fontFamily: $fontFamily ?? $this->fontFamily,
            bold: $bold ?? $this->bold,
            italic: $italic ?? $this->italic,
            underline: $underline ?? $this->underline,
            strikethrough: $strikethrough ?? $this->strikethrough,
            superscript: $superscript ?? $this->superscript,
            subscript: $subscript ?? $this->subscript,
            highlight: $highlight ?? $this->highlight,
            letterSpacingPt: $letterSpacingPt ?? $this->letterSpacingPt,
        );
    }

    public function isEmpty(): bool
    {
        return $this->sizePt === null
            && $this->color === null
            && $this->backgroundColor === null
            && $this->fontFamily === null
            && $this->highlight === null
            && $this->letterSpacingPt === null
            && ! $this->bold && ! $this->italic && ! $this->underline
            && ! $this->strikethrough && ! $this->superscript && ! $this->subscript;
    }
}
