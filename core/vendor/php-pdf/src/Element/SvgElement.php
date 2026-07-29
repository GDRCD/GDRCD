<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\Alignment;

/**
 * Vector graphics block — embeds SVG markup into the PDF.
 *
 * Supported primitives:
 *  - Shapes: rect, line, circle, ellipse, polygon, polyline
 *  - Paths: M/L/Z, C/S/Q/T curves, H/V, A arcs (full path syntax)
 *  - Text: `<text>` with positioning and styling
 *  - Transforms: translate, scale, rotate, matrix, gradientTransform
 *  - Gradients: linearGradient, radialGradient (multi-stop with stitching)
 *  - Reuse: `<defs>` and `<use>` references
 *  - Inline `<style>` CSS for class-based styling
 *
 * Attributes: fill / stroke as hex (#rgb, #rrggbb), 'none', or named
 * colors; stroke-width numeric; full opacity support via ExtGState.
 *
 * Coordinates: SVG Y axis grows down; output is transformed to PDF
 * convention (Y grows up).
 */
final readonly class SvgElement implements BlockElement
{
    public function __construct(
        public string $svgXml,
        public float $widthPt = 100.0,
        public float $heightPt = 100.0,
        public Alignment $alignment = Alignment::Start,
        public float $spaceBeforePt = 0,
        public float $spaceAfterPt = 0,
    ) {
        if ($svgXml === '') {
            throw new \InvalidArgumentException('SvgElement requires non-empty SVG content');
        }
    }
}
