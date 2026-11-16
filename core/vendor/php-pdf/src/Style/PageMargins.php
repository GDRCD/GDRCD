<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Style;

/**
 * Page margins in points (top / right / bottom / left).
 *
 * Defaults are 20mm (≈56.7pt) — a common print margin. `mirrored` swaps
 * left and right margins on even pages for two-sided book layouts.
 * `gutterPt` adds extra space on the binding edge — left on odd pages
 * (or right on even pages when mirrored).
 */
final readonly class PageMargins
{
    public function __construct(
        public float $topPt = 56.7,
        public float $rightPt = 56.7,
        public float $bottomPt = 56.7,
        public float $leftPt = 56.7,
        public bool $mirrored = false,
        public float $gutterPt = 0,
    ) {}

    /**
     * Apply the same value to all four sides.
     */
    public static function all(float $pt): self
    {
        return new self($pt, $pt, $pt, $pt);
    }

    /**
     * Build margins from millimetre values.
     */
    public static function fromMm(
        float $topMm = 20,
        float $rightMm = 20,
        float $bottomMm = 20,
        float $leftMm = 20,
    ): self {
        $toPt = static fn (float $mm): float => $mm * 2.83464567;

        return new self($toPt($topMm), $toPt($rightMm), $toPt($bottomMm), $toPt($leftMm));
    }

    /**
     * Effective left/right margins for a given 1-based page number,
     * honoring mirrored swap and gutter.
     *
     * @return array{0: float, 1: float}  [effectiveLeftPt, effectiveRightPt]
     */
    public function effectiveLeftRightFor(int $pageNumber): array
    {
        $isOdd = ($pageNumber % 2) === 1;
        if (! $this->mirrored) {
            return [$this->leftPt + $this->gutterPt, $this->rightPt];
        }
        if ($isOdd) {
            return [$this->leftPt + $this->gutterPt, $this->rightPt];
        }

        return [$this->rightPt, $this->leftPt + $this->gutterPt];
    }
}
