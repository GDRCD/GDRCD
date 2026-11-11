<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\Alignment;

/**
 * Single-series line chart through (label, value) data points. Rendered
 * with native PDF paths — strokeLine for axes, an m/l sequence for the
 * polyline, optional filled circles (~2pt radius) at data points.
 *
 * Set `smoothed: true` for Catmull-Rom spline interpolation. See
 * MultiLineChart for multi-series support.
 */
final readonly class LineChart implements BlockElement
{
    /**
     * @param  list<array{label: string, value: float, color?: string}>  $points
     */
    public function __construct(
        public array $points,
        public float $widthPt = 400.0,
        public float $heightPt = 200.0,
        public ?string $title = null,
        public float $axisLabelSizePt = 8.0,
        public float $titleSizePt = 12.0,
        public string $lineColor = '4287f5',
        public bool $showMarkers = true,
        public bool $showGridLines = false,
        public bool $smoothed = false,
        public ?float $yMin = null,
        public ?float $yMax = null,
        public ?string $xAxisTitle = null,
        public ?string $yAxisTitle = null,
        public float $axisTitleSizePt = 9.0,
        public Alignment $alignment = Alignment::Start,
        public float $spaceBeforePt = 6.0,
        public float $spaceAfterPt = 6.0,
        public float $xLabelRotationDeg = 0.0,
    ) {
        if (count($points) < 2) {
            throw new \InvalidArgumentException('LineChart requires at least 2 points');
        }
    }
}
