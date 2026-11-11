<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\Alignment;

/**
 * Area chart — a line chart with the region between the line and the
 * baseline filled. Shares multi-series schema with MultiLineChart.
 *
 * Modes:
 *  - `stacked: false` — each series filled independently from the
 *    baseline (y=0). Series may overlap, so single-series usage is
 *    recommended.
 *  - `stacked: true` — series stacked on top of the previous. Total
 *    height at each x is the sum of all series values.
 */
final readonly class AreaChart implements BlockElement
{
    /**
     * @param  list<string>  $xLabels
     * @param  list<array{name: string, color?: string, values: list<float>}>  $series
     */
    public function __construct(
        public array $xLabels,
        public array $series,
        public bool $stacked = false,
        public float $widthPt = 400.0,
        public float $heightPt = 220.0,
        public ?string $title = null,
        public float $axisLabelSizePt = 8.0,
        public float $titleSizePt = 12.0,
        public float $legendSizePt = 8.0,
        public bool $showLegend = true,
        public float $fillOpacityScale = 1.0,
        public bool $showGridLines = false,
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
        if (count($xLabels) < 2) {
            throw new \InvalidArgumentException('AreaChart requires ≥2 xLabels');
        }
        if ($series === []) {
            throw new \InvalidArgumentException('AreaChart requires ≥1 series');
        }
        $n = count($xLabels);
        foreach ($series as $s) {
            if (! isset($s['name'], $s['values'])) {
                throw new \InvalidArgumentException('Each series must have name and values');
            }
            if (count($s['values']) !== $n) {
                throw new \InvalidArgumentException(sprintf(
                    'Series "%s" has %d values, expected %d',
                    $s['name'], count($s['values']), $n,
                ));
            }
        }
    }
}
