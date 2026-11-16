<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\Alignment;

/**
 * Stacked bar chart. Each category bar is the cumulative sum of all
 * series values; segments are colored by series. Useful for composition
 * / contribution visualisation.
 *
 * Shares schema with GroupedBarChart; rendering stacks segments
 * vertically instead of placing bars side-by-side.
 */
final readonly class StackedBarChart implements BlockElement
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
            throw new \InvalidArgumentException('StackedBarChart requires bars');
        }
        if ($seriesNames === []) {
            throw new \InvalidArgumentException('StackedBarChart requires seriesNames');
        }
        $n = count($seriesNames);
        foreach ($bars as $bar) {
            if (! isset($bar['label'], $bar['values'])) {
                throw new \InvalidArgumentException('Each bar must have label and values');
            }
            if (count($bar['values']) !== $n) {
                throw new \InvalidArgumentException(sprintf(
                    'Bar "%s" has %d values, expected %d',
                    $bar['label'], count($bar['values']), $n,
                ));
            }
        }
    }
}
