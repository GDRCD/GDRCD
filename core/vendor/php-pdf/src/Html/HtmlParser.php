<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Html;

use Dskripchenko\PhpPdf\Element\BlockElement;
use Dskripchenko\PhpPdf\Element\Cell;
use Dskripchenko\PhpPdf\Element\Heading;
use Dskripchenko\PhpPdf\Element\HorizontalRule;
use Dskripchenko\PhpPdf\Element\Hyperlink;
use Dskripchenko\PhpPdf\Element\Image;
use Dskripchenko\PhpPdf\Element\InlineElement;
use Dskripchenko\PhpPdf\Element\LineBreak;
use Dskripchenko\PhpPdf\Element\ListItem;
use Dskripchenko\PhpPdf\Element\ListNode;
use Dskripchenko\PhpPdf\Element\Paragraph;
use Dskripchenko\PhpPdf\Element\Row;
use Dskripchenko\PhpPdf\Element\Run;
use Dskripchenko\PhpPdf\Element\Table;
use Dskripchenko\PhpPdf\Image\PdfImage;
use Dskripchenko\PhpPdf\Style\ListFormat;
use Dskripchenko\PhpPdf\Style\RunStyle;

/**
 * HTML5 → AST parser. Backs `Document::fromHtml()`.
 *
 * Supports:
 *  - Block tags: p, div, section, article, h1-h6, hr, ul/ol/li,
 *    table/tr/td/th (incl. thead/tbody/tfoot wrappers, caption),
 *    blockquote, pre, header, footer, nav, aside, main, figure,
 *    figcaption, address, details/summary, dl/dt/dd, center
 *  - Inline tags: span, b/strong, i/em, u, s/strike/del, sup, sub, br,
 *    wbr, img, picture, a, font, svg, code, kbd, samp, tt, var, mark,
 *    small, big, ins, cite, dfn, q, abbr
 *  - Inline CSS via `style` attribute: color, background-color,
 *    font-size, font-family, font-weight, font-style, text-decoration,
 *    text-transform, letter-spacing
 *  - Block CSS: text-align, margin, padding (shorthand + per-side),
 *    line-height, text-indent, border (shorthand + per-side)
 *
 * Not supported: external CSS, `<style>` blocks, complex selectors,
 * `@media` queries, JavaScript, forms, position: absolute/fixed, floats.
 * For class-based or `<style>` block styling, preprocess HTML through an
 * external CSS inliner (e.g. tijsverkoyen/css-to-inline-styles) to
 * convert everything to inline `style` attributes first.
 */
final class HtmlParser
{
    /**
     * Inline style stack — pushed on entering a styled tag, popped on
     * exit. Styles cascade down the tree.
     *
     * @var list<RunStyle>
     */
    private array $styleStack = [];

    /** Href of the currently-open `<a>` context, or null. */
    private ?string $linkHref = null;

    /**
     * Text-transform stack: 'none' / 'uppercase' / 'lowercase' /
     * 'capitalize'. Applied to text content as it is collected into Runs.
     *
     * @var list<string>
     */
    private array $textTransformStack = [];

    /**
     * Parse HTML string → list of BlockElements.
     *
     * @return list<BlockElement>
     */
    public function parse(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }
        $dom = $this->loadDom($html);
        $root = $dom->getElementById('_phppdf_root');
        if (! $root instanceof \DOMNode) {
            $root = $dom->documentElement;
        }
        if (! $root instanceof \DOMNode) {
            return [];
        }
        $this->styleStack = [new RunStyle];
        $this->textTransformStack = ['none'];

        return $this->walkBlocks($root);
    }

    /**
     * Convenience: parse HTML, wrap in default Section, return Document.
     */
    public static function fromHtml(string $html): \Dskripchenko\PhpPdf\Document
    {
        $parser = new self;
        $blocks = $parser->parse($html);

        return new \Dskripchenko\PhpPdf\Document(
            new \Dskripchenko\PhpPdf\Section($blocks),
        );
    }

    private function loadDom(string $html): \DOMDocument
    {
        $dom = new \DOMDocument;
        // libxml errors swallowed (HTML imperfect — non-strict parse).
        $prevErrors = libxml_use_internal_errors(true);
        try {
            // Wrap input in a synthetic root so that top-level traversal
            // consistently walks through childNodes even when the input is
            // a single element or bare text.
            // UTF-8 BOM prefix helps libxml detect encoding.
            $dom->loadHTML(
                "\xEF\xBB\xBF<div id=\"_phppdf_root\">$html</div>",
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($prevErrors);
        }

        return $dom;
    }

    /**
     * @return list<BlockElement>
     */
    private function walkBlocks(\DOMNode $parent): array
    {
        $blocks = [];
        $pendingInlines = [];

        foreach ($parent->childNodes as $node) {
            if ($this->isBlockTag($node)) {
                if ($pendingInlines !== []) {
                    $blocks[] = new Paragraph($pendingInlines);
                    $pendingInlines = [];
                }
                // Semantic containers (header/footer/nav/aside/main/
                // figure/figcaption/article/section).
                // If contains block-level children → flatten through.
                // If only inline content → treat as Paragraph.
                $tag = strtolower($node->nodeName);
                if (in_array($tag, ['header', 'footer', 'nav', 'aside', 'main',
                    'figure', 'figcaption', 'article', 'section'], true)) {
                    if ($this->hasBlockChild($node)) {
                        foreach ($this->walkBlocks($node) as $b) {
                            $blocks[] = $b;
                        }
                    } else {
                        // Inline-only — wrap in Paragraph.
                        $blocks[] = $this->parseParagraph($node);
                    }

                    continue;
                }
                if ($tag === 'dl') {
                    foreach ($this->parseDefinitionListItems($node) as $b) {
                        $blocks[] = $b;
                    }

                    continue;
                }
                // <table> caption — extract first child <caption>
                // and prepend as separate centered/bold paragraph.
                if ($tag === 'table') {
                    $captionBlock = $this->extractTableCaption($node);
                    if ($captionBlock !== null) {
                        $blocks[] = $captionBlock;
                    }
                    $blocks[] = $this->parseTable($node);

                    continue;
                }
                // <details><summary> — summary as bold heading,
                // content as indented block sequence.
                if ($tag === 'details') {
                    foreach ($this->parseDetails($node) as $b) {
                        $blocks[] = $b;
                    }

                    continue;
                }

                $block = $this->parseBlock($node);
                if ($block !== null) {
                    $blocks[] = $block;
                }
            } elseif ($this->isBlockImage($node)) {
                // <img> or <svg> with explicit block-context. Wrap pending inlines +
                // emit as block.
                if ($pendingInlines !== []) {
                    $blocks[] = new Paragraph($pendingInlines);
                    $pendingInlines = [];
                }
                $tag = strtolower($node->nodeName);
                if ($tag === 'svg') {
                    $svg = $this->parseInlineSvg($node);
                    if ($svg !== null) {
                        $blocks[] = $svg;
                    }
                } else {
                    $img = $this->parseImage($node);
                    if ($img !== null) {
                        $blocks[] = $img;
                    }
                }
            } else {
                $inlines = $this->walkInlines($node);
                $pendingInlines = array_merge($pendingInlines, $inlines);
            }
        }

        if ($pendingInlines !== []) {
            $blocks[] = new Paragraph($pendingInlines);
        }

        return $blocks;
    }

    /**
     * Detect if container has any block-level child.
     * Used for the transparent-flatten decision on semantic containers.
     */
    private function hasBlockChild(\DOMNode $node): bool
    {
        foreach ($node->childNodes as $child) {
            if ($this->isBlockTag($child)) {
                return true;
            }
        }

        return false;
    }

    private function isBlockTag(\DOMNode $node): bool
    {
        if (! $node instanceof \DOMElement) {
            return false;
        }
        $tag = strtolower($node->nodeName);

        return in_array($tag, [
            'p', 'div', 'section', 'article',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'hr', 'ul', 'ol', 'dl', 'table',
            'blockquote', 'pre',
            // HTML5 semantic block groupings.
            'header', 'footer', 'nav', 'aside', 'main',
            'figure', 'figcaption',
            // Legacy block.
            'center',
            // address (semantic block), details (collapsible).
            'address', 'details',
        ], true);
    }

    private function isBlockImage(\DOMNode $node): bool
    {
        return $node instanceof \DOMElement
            && (strtolower($node->nodeName) === 'img' || strtolower($node->nodeName) === 'svg');
    }

    private function parseBlock(\DOMElement $node): ?BlockElement
    {
        $tag = strtolower($node->nodeName);

        return match ($tag) {
            'p', 'div', 'section', 'article', 'blockquote', 'pre' => $this->parseParagraph($node),
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6' => $this->parseHeading($node, (int) $tag[1]),
            'hr' => new HorizontalRule,
            'ul' => $this->parseList($node, ordered: false),
            'ol' => $this->parseList($node, ordered: true),
            'table' => $this->parseTable($node),
            // Legacy <center> — convert to Paragraph with Alignment::Center.
            'center' => $this->parseCenterBlock($node),
            // <address> — italic paragraph (HTML5 spec semantics).
            'address' => $this->parseAddressBlock($node),
            // 'dl', 'details', semantic containers handled inline in walkBlocks
            // (multi-block flatten).
            default => null,
        };
    }

    /**
     * <address> — italic paragraph for contact info.
     */
    private function parseAddressBlock(\DOMElement $node): Paragraph
    {
        $inlines = [];
        foreach ($node->childNodes as $child) {
            $inlines = array_merge($inlines, $this->walkInlines($child));
        }
        $italicized = array_map(
            fn ($i) => $i instanceof Run
                ? new Run($i->text, $i->style->withItalic())
                : $i,
            $inlines,
        );

        return new Paragraph($italicized);
    }

    /**
     * <details> with optional <summary> — render summary as bold
     * paragraph, then content as indented block sequence.
     *
     * Note: collapsibility is not reproducible in static PDF — output always
     * shows fully expanded content.
     *
     * @return list<BlockElement>
     */
    private function parseDetails(\DOMElement $node): array
    {
        $blocks = [];
        $summaryFound = false;
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement && strtolower($child->nodeName) === 'summary') {
                $summaryFound = true;
                $inlines = [];
                foreach ($child->childNodes as $inner) {
                    $inlines = array_merge($inlines, $this->walkInlines($inner));
                }
                $bolded = array_map(
                    fn ($i) => $i instanceof Run
                        ? new Run($i->text, $i->style->withBold())
                        : $i,
                    $inlines,
                );
                $blocks[] = new Paragraph(
                    $bolded,
                    style: new \Dskripchenko\PhpPdf\Style\ParagraphStyle(
                        spaceBeforePt: 6.0,
                        spaceAfterPt: 4.0,
                    ),
                );
            } elseif ($this->isBlockTag($child)) {
                $childBlock = $this->parseBlock($child);
                if ($childBlock !== null) {
                    $blocks[] = $this->indentBlock($childBlock, 16.0);
                }
            }
            // Inline text children of details — skipped for now
            // (details usually contains block content).
        }
        if (! $summaryFound) {
            // Fallback "Details" prefix.
            $blocks = array_merge(
                [new Paragraph([new Run('Details', new \Dskripchenko\PhpPdf\Style\RunStyle(bold: true))])],
                $blocks,
            );
        }

        return $blocks;
    }

    /**
     * Add left indent to an existing paragraph block (for details content).
     */
    private function indentBlock(BlockElement $block, float $indentPt): BlockElement
    {
        if (! $block instanceof Paragraph) {
            return $block; // Heading/Table — no indent helper
        }
        $newStyle = $block->style->copy(
            indentLeftPt: $block->style->indentLeftPt + $indentPt,
        );

        return new Paragraph($block->children, $newStyle);
    }

    /**
     * <center> legacy element — emit as centered Paragraph.
     */
    private function parseCenterBlock(\DOMElement $node): Paragraph
    {
        $inlines = [];
        foreach ($node->childNodes as $child) {
            $inlines = array_merge($inlines, $this->walkInlines($child));
        }

        return new Paragraph($inlines, style: new \Dskripchenko\PhpPdf\Style\ParagraphStyle(
            alignment: \Dskripchenko\PhpPdf\Style\Alignment::Center,
        ));
    }

    /**
     * Definition list — DT (term) bold paragraphs, DD (definition)
     * indented paragraphs. Returns a flattened sequence for inclusion in
     * the parent's block list.
     *
     * @return list<BlockElement>
     */
    private function parseDefinitionListItems(\DOMElement $node): array
    {
        $blocks = [];
        foreach ($node->childNodes as $child) {
            if (! $child instanceof \DOMElement) {
                continue;
            }
            $tag = strtolower($child->nodeName);
            if ($tag === 'dt') {
                $inlines = [];
                foreach ($child->childNodes as $inner) {
                    $inlines = array_merge($inlines, $this->walkInlines($inner));
                }
                $boldedInlines = array_map(
                    fn ($i) => $i instanceof Run
                        ? new Run($i->text, $i->style->withBold())
                        : $i,
                    $inlines,
                );
                $blocks[] = new Paragraph($boldedInlines);
            } elseif ($tag === 'dd') {
                $inlines = [];
                foreach ($child->childNodes as $inner) {
                    $inlines = array_merge($inlines, $this->walkInlines($inner));
                }
                $blocks[] = new Paragraph(
                    $inlines,
                    style: new \Dskripchenko\PhpPdf\Style\ParagraphStyle(
                        indentLeftPt: 24.0,
                    ),
                );
            }
        }

        return $blocks;
    }

    private function parseParagraph(\DOMElement $node): Paragraph
    {
        // Block-level text-transform via push to stack.
        $blockTransform = $this->cssTextTransform($node);
        if ($blockTransform !== null) {
            $this->textTransformStack[] = $blockTransform;
        }
        try {
            $inlines = [];
            foreach ($node->childNodes as $child) {
                $inlines = array_merge($inlines, $this->walkInlines($child));
            }
        } finally {
            if ($blockTransform !== null) {
                array_pop($this->textTransformStack);
            }
        }

        $style = $this->parseBlockCssStyle($node);

        return new Paragraph($inlines, style: $style);
    }

    private function parseHeading(\DOMElement $node, int $level): Heading
    {
        // Same block-level transform support.
        $blockTransform = $this->cssTextTransform($node);
        if ($blockTransform !== null) {
            $this->textTransformStack[] = $blockTransform;
        }
        try {
            $inlines = [];
            foreach ($node->childNodes as $child) {
                $inlines = array_merge($inlines, $this->walkInlines($child));
            }
        } finally {
            if ($blockTransform !== null) {
                array_pop($this->textTransformStack);
            }
        }

        $style = $this->parseBlockCssStyle($node);

        // Heading anchor from id attribute if set
        // (for linkability via <a href="#anchor-id">).
        $anchor = $node->getAttribute('id') !== '' ? $node->getAttribute('id') : null;

        return new Heading($level, $inlines, $style, $anchor);
    }

    /**
     * Parse block-level CSS attributes (text-align, margin, padding,
     * background-color, line-height) → ParagraphStyle.
     */
    private function parseBlockCssStyle(\DOMElement $node): \Dskripchenko\PhpPdf\Style\ParagraphStyle
    {
        $defaults = new \Dskripchenko\PhpPdf\Style\ParagraphStyle;
        $css = $node->getAttribute('style');
        if ($css === '') {
            return $defaults;
        }
        $decl = $this->parseCssDeclarations($css);

        $alignment = $defaults->alignment;
        if (isset($decl['text-align'])) {
            $alignment = match (strtolower(trim($decl['text-align']))) {
                'left', 'start' => \Dskripchenko\PhpPdf\Style\Alignment::Start,
                'right', 'end' => \Dskripchenko\PhpPdf\Style\Alignment::End,
                'center' => \Dskripchenko\PhpPdf\Style\Alignment::Center,
                'justify' => \Dskripchenko\PhpPdf\Style\Alignment::Both,
                default => $alignment,
            };
        }

        $bgColor = $defaults->backgroundColor;
        if (isset($decl['background-color'])) {
            $parsed = $this->parseColor($decl['background-color']);
            if ($parsed !== null) {
                $bgColor = $parsed;
            }
        }

        $lineHeightMult = $defaults->lineHeightMult;
        if (isset($decl['line-height'])) {
            $lh = trim($decl['line-height']);
            if (preg_match('/^([\d.]+)$/', $lh, $m)) {
                $lineHeightMult = (float) $m[1]; // unitless = multiplier
            } elseif (preg_match('/^([\d.]+)%$/', $lh, $m)) {
                $lineHeightMult = (float) $m[1] / 100.0;
            }
        }

        // text-indent (first-line indentation).
        $indentFirstLine = $defaults->indentFirstLinePt;
        if (isset($decl['text-indent'])) {
            $parsed = $this->parseLengthOrNull($decl['text-indent']);
            if ($parsed !== null) {
                $indentFirstLine = $parsed;
            }
        }

        // border shorthand / individual sides.
        $borders = $defaults->borders;
        $borderParsed = $this->parseBorderShorthand($decl);
        if ($borderParsed !== null) {
            $borders = $borderParsed;
        }

        [$pt, $pr, $pb, $pl] = $this->parseBoxShorthand($decl['padding'] ?? null);
        $padTop = $pt ?? $this->parseLengthOrNull($decl['padding-top'] ?? '') ?? $defaults->paddingTopPt;
        $padRight = $pr ?? $this->parseLengthOrNull($decl['padding-right'] ?? '') ?? $defaults->paddingRightPt;
        $padBottom = $pb ?? $this->parseLengthOrNull($decl['padding-bottom'] ?? '') ?? $defaults->paddingBottomPt;
        $padLeft = $pl ?? $this->parseLengthOrNull($decl['padding-left'] ?? '') ?? $defaults->paddingLeftPt;

        [$mt, $mr, $mb, $ml] = $this->parseBoxShorthand($decl['margin'] ?? null);
        $spaceBefore = $mt ?? $this->parseLengthOrNull($decl['margin-top'] ?? '') ?? $defaults->spaceBeforePt;
        $spaceAfter = $mb ?? $this->parseLengthOrNull($decl['margin-bottom'] ?? '') ?? $defaults->spaceAfterPt;
        $indentLeft = $ml ?? $this->parseLengthOrNull($decl['margin-left'] ?? '') ?? $defaults->indentLeftPt;
        $indentRight = $mr ?? $this->parseLengthOrNull($decl['margin-right'] ?? '') ?? $defaults->indentRightPt;

        return new \Dskripchenko\PhpPdf\Style\ParagraphStyle(
            alignment: $alignment,
            spaceBeforePt: $spaceBefore,
            spaceAfterPt: $spaceAfter,
            indentLeftPt: $indentLeft,
            indentRightPt: $indentRight,
            indentFirstLinePt: $indentFirstLine,
            lineHeightMult: $lineHeightMult,
            lineHeightPt: $defaults->lineHeightPt,
            pageBreakBefore: $defaults->pageBreakBefore,
            borders: $borders,
            paddingTopPt: $padTop,
            paddingRightPt: $padRight,
            paddingBottomPt: $padBottom,
            paddingLeftPt: $padLeft,
            backgroundColor: $bgColor,
        );
    }

    /**
     * Parse `border: <width> <style> <color>` shorthand or
     * individual `border-top`/`border-right`/etc. Returns BorderSet or null.
     *
     * @param  array<string, string>  $decl
     */
    private function parseBorderShorthand(array $decl): ?\Dskripchenko\PhpPdf\Style\BorderSet
    {
        $allBorder = isset($decl['border']) ? $this->parseSingleBorder($decl['border']) : null;
        $top = $this->parseSingleBorder($decl['border-top'] ?? null) ?? $allBorder;
        $right = $this->parseSingleBorder($decl['border-right'] ?? null) ?? $allBorder;
        $bottom = $this->parseSingleBorder($decl['border-bottom'] ?? null) ?? $allBorder;
        $left = $this->parseSingleBorder($decl['border-left'] ?? null) ?? $allBorder;

        if ($top === null && $right === null && $bottom === null && $left === null) {
            return null;
        }

        return new \Dskripchenko\PhpPdf\Style\BorderSet(
            top: $top,
            right: $right,
            bottom: $bottom,
            left: $left,
        );
    }

    /**
     * Parse single border shorthand: "1px solid #000" → Border.
     */
    private function parseSingleBorder(?string $value): ?\Dskripchenko\PhpPdf\Style\Border
    {
        if ($value === null || trim($value) === '' || trim($value) === 'none') {
            return null;
        }
        $parts = preg_split('/\s+/', trim($value)) ?: [];
        $width = null;
        $style = \Dskripchenko\PhpPdf\Style\BorderStyle::Single;
        $color = '000000';

        foreach ($parts as $part) {
            $p = trim($part);
            // Length?
            $len = $this->parseLengthOrNull($p);
            if ($len !== null && $width === null) {
                $width = $len;

                continue;
            }
            // Style keyword?
            $styleVal = match (strtolower($p)) {
                'solid' => \Dskripchenko\PhpPdf\Style\BorderStyle::Single,
                'double' => \Dskripchenko\PhpPdf\Style\BorderStyle::Double,
                'dashed' => \Dskripchenko\PhpPdf\Style\BorderStyle::Dashed,
                'dotted' => \Dskripchenko\PhpPdf\Style\BorderStyle::Dotted,
                'none', 'hidden' => null,
                default => false,
            };
            if ($styleVal === null) {
                return null; // none → no border
            }
            if ($styleVal !== false) {
                $style = $styleVal;

                continue;
            }
            // Color?
            $parsedColor = $this->parseColor($p);
            if ($parsedColor !== null) {
                $color = $parsedColor;
            }
        }

        $sizeEighths = max(1, (int) round(($width ?? 1.0) * 8));

        return new \Dskripchenko\PhpPdf\Style\Border(
            style: $style,
            sizeEighthsOfPoint: $sizeEighths,
            color: $color,
        );
    }

    /**
     * Parse CSS box shorthand (margin/padding) — 1/2/3/4 value semantics.
     *
     * @return array{0: ?float, 1: ?float, 2: ?float, 3: ?float}  [top, right, bottom, left] or null if not set
     */
    private function parseBoxShorthand(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [null, null, null, null];
        }
        $parts = preg_split('/\s+/', trim($value)) ?: [];
        $parsed = array_map(fn ($p) => $this->parseLengthOrNull($p), $parts);

        // CSS box shorthand semantics:
        // 1 value: all sides
        // 2 values: vertical, horizontal
        // 3 values: top, horizontal, bottom
        // 4 values: top, right, bottom, left
        return match (count($parsed)) {
            1 => [$parsed[0], $parsed[0], $parsed[0], $parsed[0]],
            2 => [$parsed[0], $parsed[1], $parsed[0], $parsed[1]],
            3 => [$parsed[0], $parsed[1], $parsed[2], $parsed[1]],
            4 => [$parsed[0], $parsed[1], $parsed[2], $parsed[3]],
            default => [null, null, null, null],
        };
    }

    private function parseList(\DOMElement $node, bool $ordered): ListNode
    {
        $items = [];
        foreach ($node->childNodes as $child) {
            if (! $child instanceof \DOMElement) {
                continue;
            }
            if (strtolower($child->nodeName) !== 'li') {
                continue;
            }
            $items[] = $this->parseListItem($child);
        }

        return new ListNode(
            items: $items,
            format: $ordered ? ListFormat::Decimal : ListFormat::Bullet,
        );
    }

    private function parseListItem(\DOMElement $node): ListItem
    {
        // Separate inline content from nested list.
        $blocks = [];
        $pendingInlines = [];
        $nestedList = null;
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $name = strtolower($child->nodeName);
                if ($name === 'ul' || $name === 'ol') {
                    $nestedList = $this->parseList($child, ordered: $name === 'ol');

                    continue;
                }
                if ($this->isBlockTag($child)) {
                    if ($pendingInlines !== []) {
                        $blocks[] = new Paragraph($pendingInlines);
                        $pendingInlines = [];
                    }
                    $b = $this->parseBlock($child);
                    if ($b !== null) {
                        $blocks[] = $b;
                    }

                    continue;
                }
            }
            $inlines = $this->walkInlines($child);
            $pendingInlines = array_merge($pendingInlines, $inlines);
        }
        if ($pendingInlines !== []) {
            $blocks[] = new Paragraph($pendingInlines);
        }
        if ($blocks === []) {
            $blocks[] = new Paragraph([new Run('')]);
        }

        return new ListItem(children: $blocks, nestedList: $nestedList);
    }

    private function parseTable(\DOMElement $node): Table
    {
        $rows = [];
        foreach ($node->childNodes as $child) {
            if (! $child instanceof \DOMElement) {
                continue;
            }
            $name = strtolower($child->nodeName);
            if ($name === 'tr') {
                $rows[] = $this->parseTableRow($child);
            } elseif ($name === 'thead' || $name === 'tbody' || $name === 'tfoot') {
                // Walk into wrapper, find <tr>s.
                foreach ($child->childNodes as $tr) {
                    if ($tr instanceof \DOMElement && strtolower($tr->nodeName) === 'tr') {
                        $rows[] = $this->parseTableRow($tr);
                    }
                }
            }
            // <caption> and <colgroup> skipped — caption extracted separately
            // (in walkBlocks); colgroup not yet supported.
        }

        return new Table($rows);
    }

    /**
     * Extract `<caption>` from `<table>` if present, render as
     * centered bold paragraph (rendered before table). Returns null if
     * no caption.
     */
    private function extractTableCaption(\DOMElement $tableNode): ?Paragraph
    {
        foreach ($tableNode->childNodes as $child) {
            if ($child instanceof \DOMElement && strtolower($child->nodeName) === 'caption') {
                $inlines = [];
                foreach ($child->childNodes as $inner) {
                    $inlines = array_merge($inlines, $this->walkInlines($inner));
                }
                // Bold + centered.
                $boldedInlines = array_map(
                    fn ($i) => $i instanceof Run
                        ? new Run($i->text, $i->style->withBold())
                        : $i,
                    $inlines,
                );

                return new Paragraph(
                    $boldedInlines,
                    style: new \Dskripchenko\PhpPdf\Style\ParagraphStyle(
                        alignment: \Dskripchenko\PhpPdf\Style\Alignment::Center,
                        spaceAfterPt: 4.0,
                    ),
                );
            }
        }

        return null;
    }

    private function parseTableRow(\DOMElement $tr): Row
    {
        $cells = [];
        foreach ($tr->childNodes as $td) {
            if (! $td instanceof \DOMElement) {
                continue;
            }
            $name = strtolower($td->nodeName);
            if ($name !== 'td' && $name !== 'th') {
                continue;
            }
            $cells[] = $this->parseTableCell($td);
        }

        return new Row($cells);
    }

    private function parseTableCell(\DOMElement $node): Cell
    {
        $colSpan = max(1, (int) $node->getAttribute('colspan'));
        $rowSpan = max(1, (int) $node->getAttribute('rowspan'));

        $children = $this->walkBlocks($node);
        if ($children === []) {
            $children = [new Paragraph([new Run('')])];
        }

        return new Cell(children: $children, columnSpan: $colSpan, rowSpan: $rowSpan);
    }

    /**
     * @return list<InlineElement>
     */
    private function walkInlines(\DOMNode $node): array
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $text = $this->normalizeWhitespace($node->nodeValue ?? '');
            if ($text === '') {
                return [];
            }
            // Apply current text-transform.
            $text = $this->applyTextTransform($text);

            return [new Run($text, $this->currentStyle())];
        }

        if (! $node instanceof \DOMElement) {
            return [];
        }

        $tag = strtolower($node->nodeName);

        // Self-closing inline tags.
        if ($tag === 'br') {
            return [new LineBreak];
        }
        // <wbr> — word break opportunity (soft hyphen-like marker).
        // Render as U+00AD soft hyphen char.
        if ($tag === 'wbr') {
            return [new Run("\u{00AD}", $this->currentStyle())];
        }
        if ($tag === 'img') {
            $img = $this->parseImage($node);

            return $img !== null ? [$img] : [];
        }
        // <picture> — emit first descendant <img> as fallback.
        // libxml may nest <img> inside <source> (since <source> isn't
        // void-closed without </source>), so search recursively.
        if ($tag === 'picture') {
            $img = $this->findFirstImg($node);

            return $img !== null ? [$img] : [];
        }
        // Inline <svg> — extract outerHTML and wrap in SvgElement.
        if ($tag === 'svg') {
            $svg = $this->parseInlineSvg($node);

            return $svg !== null ? [$svg] : [];
        }

        // Anchor — wraps inner inlines.
        if ($tag === 'a') {
            return $this->parseInlineAnchor($node);
        }

        // <font> legacy tag — color/face/size attributes.
        if ($tag === 'font') {
            return $this->parseFontTag($node);
        }

        // Styled inline tags.
        $newStyle = $this->styleForTag($this->currentStyle(), $tag);
        $newStyle = $this->applyInlineCssStyle($newStyle, $node);

        // text-transform per-tag pushed to stack.
        $newTransform = $this->cssTextTransform($node) ?? $this->currentTransform();

        $this->styleStack[] = $newStyle;
        $this->textTransformStack[] = $newTransform;
        try {
            $result = [];
            foreach ($node->childNodes as $child) {
                $result = array_merge($result, $this->walkInlines($child));
            }

            return $result;
        } finally {
            array_pop($this->styleStack);
            array_pop($this->textTransformStack);
        }
    }

    /**
     * Detect text-transform CSS on element. Returns 'uppercase'/
     * 'lowercase'/'capitalize'/'none' or null if not set.
     */
    private function cssTextTransform(\DOMElement $node): ?string
    {
        $css = $node->getAttribute('style');
        if ($css === '') {
            return null;
        }
        $decl = $this->parseCssDeclarations($css);
        if (! isset($decl['text-transform'])) {
            return null;
        }

        return match (strtolower(trim($decl['text-transform']))) {
            'uppercase' => 'uppercase',
            'lowercase' => 'lowercase',
            'capitalize' => 'capitalize',
            'none' => 'none',
            default => null,
        };
    }

    private function currentTransform(): string
    {
        return end($this->textTransformStack) ?: 'none';
    }

    private function applyTextTransform(string $text): string
    {
        return match ($this->currentTransform()) {
            'uppercase' => mb_strtoupper($text, 'UTF-8'),
            'lowercase' => mb_strtolower($text, 'UTF-8'),
            'capitalize' => mb_convert_case($text, MB_CASE_TITLE_SIMPLE, 'UTF-8'),
            default => $text,
        };
    }

    /**
     * Legacy <font color="..." face="..." size="..."> tag.
     *
     * HTML `size` attribute is 1-7 mapping to relative sizes; we map to
     * absolute pt sizes:
     *   1 → 8pt, 2 → 10pt, 3 → 12pt (default), 4 → 14pt,
     *   5 → 18pt, 6 → 24pt, 7 → 36pt.
     *
     * @return list<InlineElement>
     */
    private function parseFontTag(\DOMElement $node): array
    {
        $current = $this->currentStyle();
        $color = $current->color;
        $fontFamily = $current->fontFamily;
        $size = $current->sizePt;

        $attrColor = $node->getAttribute('color');
        if ($attrColor !== '') {
            $parsed = $this->parseColor($attrColor);
            if ($parsed !== null) {
                $color = $parsed;
            }
        }

        $attrFace = $node->getAttribute('face');
        if ($attrFace !== '') {
            $fontFamily = trim(explode(',', $attrFace)[0], " \"'");
        }

        $attrSize = $node->getAttribute('size');
        if ($attrSize !== '') {
            $sizeMap = [1 => 8.0, 2 => 10.0, 3 => 12.0, 4 => 14.0, 5 => 18.0, 6 => 24.0, 7 => 36.0];
            $intSize = (int) $attrSize;
            if (isset($sizeMap[$intSize])) {
                $size = $sizeMap[$intSize];
            }
        }

        $newStyle = new RunStyle(
            sizePt: $size,
            color: $color,
            backgroundColor: $current->backgroundColor,
            fontFamily: $fontFamily,
            bold: $current->bold,
            italic: $current->italic,
            underline: $current->underline,
            strikethrough: $current->strikethrough,
            superscript: $current->superscript,
            subscript: $current->subscript,
            letterSpacingPt: $current->letterSpacingPt,
        );

        $this->styleStack[] = $newStyle;
        try {
            $result = [];
            foreach ($node->childNodes as $child) {
                $result = array_merge($result, $this->walkInlines($child));
            }

            return $result;
        } finally {
            array_pop($this->styleStack);
        }
    }

    /**
     * @return list<InlineElement>
     */
    private function parseInlineAnchor(\DOMElement $node): array
    {
        $href = $node->getAttribute('href');
        if ($href === '') {
            // Empty <a> — treat as span.
            $result = [];
            foreach ($node->childNodes as $child) {
                $result = array_merge($result, $this->walkInlines($child));
            }

            return $result;
        }

        // Build inner inlines with link-styled default (blue + underline).
        $linkStyle = new RunStyle(
            color: $this->currentStyle()->color ?? '0000ee',
            underline: true,
        );

        $previous = $this->linkHref;
        $this->linkHref = $href;
        $this->styleStack[] = $linkStyle;
        try {
            $children = [];
            foreach ($node->childNodes as $child) {
                $children = array_merge($children, $this->walkInlines($child));
            }
            if ($children === []) {
                $children = [new Run($href, $linkStyle)];
            }
        } finally {
            array_pop($this->styleStack);
            $this->linkHref = $previous;
        }

        if (str_starts_with($href, '#')) {
            return [Hyperlink::internal(substr($href, 1), $children)];
        }

        return [Hyperlink::external($href, $children)];
    }

    /**
     * Parse inline <svg> element — extract outerHTML via DOM
     * serialization, wrap in SvgElement.
     */
    private function parseInlineSvg(\DOMElement $node): ?\Dskripchenko\PhpPdf\Element\SvgElement
    {
        $svgXml = $node->ownerDocument?->saveXML($node);
        if ($svgXml === false || $svgXml === null) {
            return null;
        }
        // Width/height from attributes. Per SVG-in-HTML spec, unitless
        // values treated as px (we convert to pt: 1px = 0.75pt @ 96 DPI).
        $widthPt = $this->parseSvgDimension($node->getAttribute('width')) ?? 100.0;
        $heightPt = $this->parseSvgDimension($node->getAttribute('height')) ?? 100.0;

        try {
            return new \Dskripchenko\PhpPdf\Element\SvgElement(
                svgXml: $svgXml,
                widthPt: $widthPt,
                heightPt: $heightPt,
            );
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Parse SVG dimension attribute. Unitless = px (per SVG spec
     * in HTML context), explicit units honored via parseLengthOrNull.
     */
    private function parseSvgDimension(string $value): ?float
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^([\d.]+)$/', $value, $m)) {
            return (float) $m[1] * 0.75; // px → pt
        }

        return $this->parseLengthOrNull($value);
    }

    /**
     * Recursive search for the first <img> descendant.
     */
    private function findFirstImg(\DOMNode $node): ?Image
    {
        if ($node instanceof \DOMElement && strtolower($node->nodeName) === 'img') {
            return $this->parseImage($node);
        }
        foreach ($node->childNodes as $child) {
            $found = $this->findFirstImg($child);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private function parseImage(\DOMElement $node): ?Image
    {
        $src = $node->getAttribute('src');
        if ($src === '') {
            return null;
        }
        $alt = $node->getAttribute('alt') ?: null;

        $width = $this->parseLengthOrNull($node->getAttribute('width'));
        $height = $this->parseLengthOrNull($node->getAttribute('height'));

        try {
            // Data URL: data:image/png;base64,...
            if (str_starts_with($src, 'data:')) {
                $pdfImg = $this->parseDataUrl($src);
            } elseif (is_readable($src)) {
                $pdfImg = PdfImage::fromPath($src);
            } else {
                return null; // Cannot load image — skip.
            }
        } catch (\Throwable) {
            return null;
        }

        return new Image(
            source: $pdfImg,
            widthPt: $width,
            heightPt: $height,
            altText: $alt,
        );
    }

    private function parseDataUrl(string $url): ?PdfImage
    {
        if (! preg_match('@^data:image/[^;]+;base64,(.+)$@', $url, $m)) {
            return null;
        }
        $bytes = base64_decode($m[1], true);
        if ($bytes === false) {
            return null;
        }

        return PdfImage::fromBytes($bytes);
    }

    private function currentStyle(): RunStyle
    {
        return end($this->styleStack) ?: new RunStyle;
    }

    private function styleForTag(RunStyle $current, string $tag): RunStyle
    {
        return match ($tag) {
            // Basic semantic styling.
            'b', 'strong' => $current->withBold(),
            'i', 'em' => $current->withItalic(),
            'u' => $current->withUnderline(),
            's', 'strike', 'del' => $current->withStrikethrough(),
            'sup' => $current->withSuperscript(),
            'sub' => $current->withSubscript(),
            // Extended semantic inline tags.
            'code', 'kbd', 'samp', 'tt', 'var' => new RunStyle(
                sizePt: $current->sizePt,
                color: $current->color,
                backgroundColor: $current->backgroundColor,
                fontFamily: 'Courier', // monospace family
                bold: $current->bold,
                italic: $tag === 'var' ? true : $current->italic,
                underline: $current->underline,
                strikethrough: $current->strikethrough,
                superscript: $current->superscript,
                subscript: $current->subscript,
                letterSpacingPt: $current->letterSpacingPt,
            ),
            'mark' => new RunStyle(
                sizePt: $current->sizePt,
                color: $current->color,
                backgroundColor: 'ffff00', // yellow highlight per HTML5 spec
                fontFamily: $current->fontFamily,
                bold: $current->bold,
                italic: $current->italic,
                underline: $current->underline,
                strikethrough: $current->strikethrough,
                superscript: $current->superscript,
                subscript: $current->subscript,
                letterSpacingPt: $current->letterSpacingPt,
            ),
            'small' => new RunStyle(
                sizePt: ($current->sizePt ?? 12.0) * 0.83, // ~10pt from 12pt base
                color: $current->color,
                backgroundColor: $current->backgroundColor,
                fontFamily: $current->fontFamily,
                bold: $current->bold,
                italic: $current->italic,
                underline: $current->underline,
                strikethrough: $current->strikethrough,
                superscript: $current->superscript,
                subscript: $current->subscript,
                letterSpacingPt: $current->letterSpacingPt,
            ),
            'big' => new RunStyle(
                sizePt: ($current->sizePt ?? 12.0) * 1.2, // ~14.4pt from 12pt base
                color: $current->color,
                backgroundColor: $current->backgroundColor,
                fontFamily: $current->fontFamily,
                bold: $current->bold,
                italic: $current->italic,
                underline: $current->underline,
                strikethrough: $current->strikethrough,
                superscript: $current->superscript,
                subscript: $current->subscript,
                letterSpacingPt: $current->letterSpacingPt,
            ),
            'ins' => $current->withUnderline(), // inserted text — underlined
            'cite', 'dfn', 'q' => $current->withItalic(), // citations/definitions/inline-quotes
            'abbr' => $current->withUnderline(), // abbreviation — dotted underline (we use plain)
            default => $current,
        };
    }

    private function applyInlineCssStyle(RunStyle $current, \DOMElement $node): RunStyle
    {
        $css = $node->getAttribute('style');
        if ($css === '') {
            return $current;
        }
        $decl = $this->parseCssDeclarations($css);

        $size = $current->sizePt;
        if (isset($decl['font-size'])) {
            $parsed = $this->parseFontSize($decl['font-size']);
            if ($parsed !== null) {
                $size = $parsed;
            }
        }

        $color = $current->color;
        if (isset($decl['color'])) {
            $parsed = $this->parseColor($decl['color']);
            if ($parsed !== null) {
                $color = $parsed;
            }
        }

        $bgColor = $current->backgroundColor;
        if (isset($decl['background-color'])) {
            $parsed = $this->parseColor($decl['background-color']);
            if ($parsed !== null) {
                $bgColor = $parsed;
            }
        }

        $fontFamily = $current->fontFamily;
        if (isset($decl['font-family'])) {
            $fontFamily = trim(explode(',', $decl['font-family'])[0], " \"'");
        }

        $bold = $current->bold;
        if (isset($decl['font-weight'])) {
            $w = trim($decl['font-weight']);
            $bold = $bold || in_array($w, ['bold', 'bolder', '700', '800', '900'], true);
        }

        $italic = $current->italic;
        if (isset($decl['font-style'])) {
            $italic = $italic || trim($decl['font-style']) === 'italic';
        }

        $underline = $current->underline;
        $strikethrough = $current->strikethrough;
        if (isset($decl['text-decoration']) || isset($decl['text-decoration-line'])) {
            $td = $decl['text-decoration'] ?? $decl['text-decoration-line'];
            if (str_contains($td, 'underline')) {
                $underline = true;
            }
            if (str_contains($td, 'line-through') || str_contains($td, 'strike')) {
                $strikethrough = true;
            }
        }

        $letterSpacing = $current->letterSpacingPt;
        if (isset($decl['letter-spacing'])) {
            $parsed = $this->parseLengthOrNull($decl['letter-spacing']);
            if ($parsed !== null) {
                $letterSpacing = $parsed;
            }
        }

        return new RunStyle(
            sizePt: $size,
            color: $color,
            backgroundColor: $bgColor,
            fontFamily: $fontFamily,
            bold: $bold,
            italic: $italic,
            underline: $underline,
            strikethrough: $strikethrough,
            superscript: $current->superscript,
            subscript: $current->subscript,
            letterSpacingPt: $letterSpacing,
        );
    }

    /**
     * @return array<string, string>
     */
    private function parseCssDeclarations(string $css): array
    {
        $result = [];
        foreach (explode(';', $css) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $colon = strpos($part, ':');
            if ($colon === false) {
                continue;
            }
            $key = strtolower(trim(substr($part, 0, $colon)));
            $value = trim(substr($part, $colon + 1));
            $result[$key] = $value;
        }

        return $result;
    }

    private function parseColor(string $css): ?string
    {
        $css = trim($css);
        if (preg_match('/^#([0-9a-f]{6})$/i', $css, $m)) {
            return strtolower($m[1]);
        }
        if (preg_match('/^#([0-9a-f]{3})$/i', $css, $m)) {
            $h = $m[1];

            return strtolower($h[0].$h[0].$h[1].$h[1].$h[2].$h[2]);
        }
        if (preg_match('/^rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$/i', $css, $m)) {
            return sprintf('%02x%02x%02x', (int) $m[1], (int) $m[2], (int) $m[3]);
        }
        $named = [
            'black' => '000000', 'white' => 'ffffff', 'gray' => '808080', 'grey' => '808080',
            'red' => 'ff0000', 'green' => '008000', 'blue' => '0000ff',
            'yellow' => 'ffff00', 'cyan' => '00ffff', 'magenta' => 'ff00ff',
            'silver' => 'c0c0c0', 'maroon' => '800000', 'olive' => '808000',
            'purple' => '800080', 'teal' => '008080', 'navy' => '000080',
            'lime' => '00ff00', 'aqua' => '00ffff', 'fuchsia' => 'ff00ff',
            'orange' => 'ffa500', 'pink' => 'ffc0cb', 'brown' => 'a52a2a',
        ];

        return $named[strtolower($css)] ?? null;
    }

    private function parseFontSize(string $css): ?float
    {
        return $this->parseLengthOrNull($css);
    }

    private function parseLengthOrNull(string $css): ?float
    {
        $css = trim($css);
        if ($css === '') {
            return null;
        }
        if (preg_match('/^([\d.]+)\s*pt$/i', $css, $m)) {
            return (float) $m[1];
        }
        if (preg_match('/^([\d.]+)\s*px$/i', $css, $m)) {
            return (float) $m[1] * 0.75; // 1px ≈ 0.75pt @ 96dpi
        }
        if (preg_match('/^([\d.]+)\s*em$/i', $css, $m)) {
            return (float) $m[1] * 12.0; // approx (assumes 12pt base)
        }
        if (preg_match('/^([\d.]+)\s*mm$/i', $css, $m)) {
            return (float) $m[1] * 72.0 / 25.4;
        }
        if (preg_match('/^([\d.]+)\s*cm$/i', $css, $m)) {
            return (float) $m[1] * 72.0 / 2.54;
        }
        if (preg_match('/^([\d.]+)\s*in$/i', $css, $m)) {
            return (float) $m[1] * 72.0;
        }
        if (preg_match('/^([\d.]+)$/', $css, $m)) {
            return (float) $m[1]; // unit-less default to pt
        }

        return null;
    }

    private function normalizeWhitespace(string $text): string
    {
        // Collapse runs of whitespace to single space, preserve leading/trailing
        // significant whitespace (HTML spec — content whitespace).
        $text = (string) preg_replace('/\s+/u', ' ', $text);

        return $text === ' ' ? '' : $text;
    }
}
