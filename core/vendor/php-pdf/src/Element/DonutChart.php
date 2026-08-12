<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\Alignment;

/**
 * Donut chart — pie chart with a transparent central hole.
 *
 * Inner radius is controlled by `$innerRatio` (0..1; default 0.5 = half
 * radius). Slice schema is identical to PieChart.
 */
final readonly class DonutChart implements BlockElement
{
    /**
     * @param  list<array{label: string, value: float, color?: string}>  $slices
     */
    public function __construct(
        public array $slices,
        public float $sizePt = 200.0,
        public float $innerRatio = 0.5,
        public ?string $title = null,
        public float $titleSizePt = 12.0,
        public float $legendSizePt = 8.0,
        public bool $showLegend = true,
        public Alignment $alignment = Alignment::Start,
        public float $spaceBeforePt = 6.0,
        public float $spaceAfterPt = 6.0,
    ) {
        if ($slices === []) {
            throw new \InvalidArgumentException('DonutChart requires slices');
        }
        if ($innerRatio < 0 || $innerRatio >= 1) {
            throw new \InvalidArgumentException('DonutChart innerRatio must be in [0, 1)');
        }
        foreach ($slices as $slice) {
            if (! isset($slice['label'], $slice['value'])) {
                throw new \InvalidArgumentException('Each slice must have label and value');
            }
            if ($slice['value'] < 0) {
                throw new \InvalidArgumentException('Slice value must be non-negative');
            }
        }
    }
}
