<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\Alignment;

/**
 * Multi-series line chart. Each series is rendered as a separate polyline.
 *
 * Example schema:
 *   xLabels = ['Jan', 'Feb', 'Mar']
 *   series  = [
 *     ['name' => 'Sales', 'color' => '4287f5', 'values' => [100, 250, 175]],
 *     ['name' => 'Costs', 'color' => 'f56242', 'values' => [80, 200, 150]],
 *   ]
 */
final readonly class MultiLineChart implements BlockElement
{
    /**
     * @param  list<string>  $xLabels
     * @param  list<array{name: string, color?: string, values: list<float>}>  $series
     */
    public function __construct(
        public array $xLabels,
        public array $series,
        public float $widthPt = 400.0,
        public float $heightPt = 220.0,
        public ?string $title = null,
        public float $axisLabelSizePt = 8.0,
        public float $titleSizePt = 12.0,
        public float $legendSizePt = 8.0,
        public bool $showLegend = true,
        public bool $showMarkers = true,
        public bool $showGridLines = false,
        public ?string $xAxisTitle = null,
        public ?string $yAxisTitle = null,
        public float $axisTitleSizePt = 9.0,
        public Alignment $alignment = Alignment::Start,
        public float $spaceBeforePt = 6.0,
        public float $spaceAfterPt = 6.0,
        public float $xLabelRotationDeg = 0.0,
    ) {
        if (count($xLabels) < 2) {
            throw new \InvalidArgumentException('MultiLineChart requires ≥2 xLabels');
        }
        if ($series === []) {
            throw new \InvalidArgumentException('MultiLineChart requires ≥1 series');
        }
        $n = count($xLabels);
        foreach ($series as $s) {
            if (! isset($s['name'], $s['values'])) {
                throw new \InvalidArgumentException('Each series must have name and values');
            }
            if (count($s['values']) !== $n) {
                throw new \InvalidArgumentException(sprintf(
                    'Series "%s" has %d values, expected %d (matching xLabels)',
                    $s['name'], count($s['values']), $n,
                ));
            }
        }
    }
}
