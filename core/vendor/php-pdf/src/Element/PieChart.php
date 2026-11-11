<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\Alignment;

/**
 * Pie chart. Sectors are rendered as cubic Bezier arcs (sub-arcs ≤ 90°),
 * producing true curves rather than polygons. Optional features:
 *  - Exploded slices via `slice.explode` (radial offset)
 *  - Perimeter labels with leader lines (`showPerimeterLabels: true`)
 *
 * See DonutChart for the variant with a transparent center.
 */
final readonly class PieChart implements BlockElement
{
    /**
     * @param  list<array{label: string, value: float, color?: string, explode?: bool|float}>  $slices
     *   `explode: true` applies a standard ~8% radius offset; a float gives
     *   a custom fraction of radius (0.05 = 5%, 0.2 = 20%).
     * @param  bool  $showPerimeterLabels  Draw labels on the perimeter with
     *                                     leader lines instead of (or in
     *                                     addition to) the sidebar legend.
     * @param  float $minLabelAngleDeg  Skip perimeter labels for slices with
     *                                  angle below this threshold (avoids
     *                                  overlap on small slices).
     */
    public function __construct(
        public array $slices,
        public float $sizePt = 200.0,
        public ?string $title = null,
        public float $titleSizePt = 12.0,
        public float $legendSizePt = 8.0,
        public bool $showLegend = true,
        public Alignment $alignment = Alignment::Start,
        public float $spaceBeforePt = 6.0,
        public float $spaceAfterPt = 6.0,
        public bool $showPerimeterLabels = false,
        public float $perimeterLabelSizePt = 8.0,
        public float $minLabelAngleDeg = 8.0,
    ) {
        if ($slices === []) {
            throw new \InvalidArgumentException('PieChart requires at least one slice');
        }
        foreach ($slices as $slice) {
            if (! isset($slice['label']) || ! isset($slice['value'])) {
                throw new \InvalidArgumentException('PieChart slice must have label and value');
            }
            if ($slice['value'] < 0) {
                throw new \InvalidArgumentException('PieChart slice value must be non-negative');
            }
        }
    }
}
