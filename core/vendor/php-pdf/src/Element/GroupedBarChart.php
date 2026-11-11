<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\Alignment;

/**
 * Multi-series grouped bar chart. Each label has N bars side-by-side,
 * one per series. A legend in the top-right shows series names and colors.
 *
 * Example schema:
 *   bars         = [['label' => 'Q1', 'values' => [100, 200, 150]], ...]
 *   seriesNames  = ['Sales', 'Costs', 'Profit']
 *   seriesColors = ['4287f5', 'f56242', '42f55a']   // optional
 */
final readonly class GroupedBarChart implements BlockElement
{
    /**
     * @param  list<array{label: string, values: list<float>}>  $bars
     * @param  list<string>  $seriesNames
     * @param  list<string>  $seriesColors
     */
    public function __construct(
        public array $bars,
        public array $seriesNames,
        public array $seriesColors = [],
        public float $widthPt = 400.0,
        public float $heightPt = 220.0,
        public ?string $title = null,
        public float $axisLabelSizePt = 8.0,
        public float $titleSizePt = 12.0,
        public float $legendSizePt = 8.0,
        public bool $showLegend = true,
        public bool $showGridLines = false,
        public ?string $xAxisTitle = null,
        public ?string $yAxisTitle = null,
        public float $axisTitleSizePt = 9.0,
        public Alignment $alignment = Alignment::Start,
        public float $spaceBeforePt = 6.0,
        public float $spaceAfterPt = 6.0,
        public float $xLabelRotationDeg = 0.0,
    ) {
        if ($bars === []) {
            throw new \InvalidArgumentException('GroupedBarChart requires bars');
        }
        if ($seriesNames === []) {
            throw new \InvalidArgumentException('GroupedBarChart requires seriesNames');
        }
        $seriesCount = count($seriesNames);
        foreach ($bars as $bar) {
            if (! isset($bar['label'], $bar['values'])) {
                throw new \InvalidArgumentException('Each bar must have label and values');
            }
            if (count($bar['values']) !== $seriesCount) {
                throw new \InvalidArgumentException(sprintf(
                    'Bar "%s" has %d values, expected %d (matching seriesNames)',
                    $bar['label'], count($bar['values']), $seriesCount,
                ));
            }
        }
    }
}
