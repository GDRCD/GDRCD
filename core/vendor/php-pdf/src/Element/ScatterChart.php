<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\Alignment;

/**
 * Scatter chart — 2D point cloud with no connecting lines.
 *
 * Each series renders as marker dots; both x and y axes are auto-scaled
 * to the data range.
 *
 * Example schema:
 *   series = [
 *     ['name' => 'Group A', 'color' => '4287f5',
 *      'points' => [['x' => 1.0, 'y' => 5.0], ...]],
 *     ...
 *   ]
 */
final readonly class ScatterChart implements BlockElement
{
    /**
     * @param  list<array{name: string, color?: string, points: list<array{x: float, y: float}>}>  $series
     */
    public function __construct(
        public array $series,
        public float $widthPt = 400.0,
        public float $heightPt = 250.0,
        public ?string $title = null,
        public ?string $xAxisLabel = null,
        public ?string $yAxisLabel = null,
        public float $axisLabelSizePt = 8.0,
        public float $titleSizePt = 12.0,
        public float $legendSizePt = 8.0,
        public bool $showLegend = true,
        public float $markerSize = 4.0,
        public bool $showGridLines = false,
        public ?string $xAxisTitle = null,
        public ?string $yAxisTitle = null,
        public float $axisTitleSizePt = 9.0,
        public Alignment $alignment = Alignment::Start,
        public float $spaceBeforePt = 6.0,
        public float $spaceAfterPt = 6.0,
    ) {
        if ($series === []) {
            throw new \InvalidArgumentException('ScatterChart requires at least 1 series');
        }
        foreach ($series as $s) {
            if (! isset($s['name'], $s['points'])) {
                throw new \InvalidArgumentException('Each series must have name and points');
            }
            if ($s['points'] === []) {
                throw new \InvalidArgumentException('Series points cannot be empty');
            }
        }
    }
}
