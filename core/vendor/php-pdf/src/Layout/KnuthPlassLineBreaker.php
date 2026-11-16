<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Layout;

/**
 * Knuth-Plass optimal line breaking.
 *
 * Uses the box-glue-penalty model with dynamic programming to find a
 * globally-optimal set of break points that minimize "demerits" (badness²
 * over all lines).
 *
 * Vs greedy `LineBreaker`:
 *  - Greedy: fit as many words as possible into each line in turn. May
 *    leave a poor last line.
 *  - K-P: optimize sum badness² across all lines globally. Produces
 *    typographically uniform spacing.
 *
 * Algorithm (Knuth, Plass — "Breaking paragraphs into lines", 1981):
 *  1. Tokenize: each word → Box; each space → Glue (width=space_w,
 *     stretchability=0.5·space_w, shrinkability=0.33·space_w).
 *  2. Forward DP: f[i] = min demerits to break at position i.
 *     f[i] = min over j<i of (f[j] + demerits(j..i)).
 *  3. For each candidate (j..i) compute adjustment ratio r:
 *     r = (target - L) / Y  if L < target (stretching)
 *     r = (L - target) / Z  if L > target (shrinking, negative r)
 *     r = 0                 if L == target
 *     Skip if r > 10 (too loose) or r < -1 (cannot shrink enough).
 *  4. Badness = 100·|r|³. Demerits = (1 + badness)².
 *  5. Last-line exception: r = 0 if line is last paragraph line (no penalty
 *     for short ragged-right finish).
 *  6. Backward pass: reconstruct break points from f[n] → 0.
 *
 * Fallback: if no admissible break-set exists for the whole parameter
 * sequence (e.g., word wider than maxWidth), gracefully degrade to greedy
 * LineBreaker.
 *
 * Not implemented:
 *  - Hyphenation penalty items (hyphenation is not performed)
 *  - Loose/tight class transitions
 *  - Looseness parameter (force longer/shorter paragraphs)
 */
final class KnuthPlassLineBreaker
{
    /**
     * @param  float  $stretchRatio  Glue stretch as fraction of space width.
     * @param  float  $shrinkRatio   Glue shrink as fraction of space width.
     */
    public function __construct(
        private readonly TextMeasurer $measurer,
        private readonly float $maxWidthPt,
        private readonly float $stretchRatio = 0.5,
        private readonly float $shrinkRatio = 0.33,
    ) {}

    /**
     * @return list<string>
     */
    public function wrap(string $text): array
    {
        $lines = [];
        foreach (explode("\n", $text) as $paragraph) {
            $wrapped = $this->wrapParagraph($paragraph);
            foreach ($wrapped as $line) {
                $lines[] = $line;
            }
            if ($wrapped === []) {
                $lines[] = '';
            }
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    private function wrapParagraph(string $paragraph): array
    {
        $paragraph = trim($paragraph);
        if ($paragraph === '') {
            return [''];
        }

        $words = preg_split('/\s+/u', $paragraph) ?: [];
        $n = count($words);
        if ($n === 0) {
            return [''];
        }
        if ($n === 1) {
            // Single word — may be wider than the line, fallback to greedy.
            if ($this->measurer->widthPt($words[0]) > $this->maxWidthPt) {
                return $this->greedyFallback($paragraph);
            }

            return [$words[0]];
        }

        $widths = [];
        $hasOversizeWord = false;
        foreach ($words as $w) {
            $width = $this->measurer->widthPt($w);
            if ($width > $this->maxWidthPt) {
                $hasOversizeWord = true;
            }
            $widths[] = $width;
        }

        // If there is a word wider than the line, K-P cannot find a feasible
        // break-set; fallback to greedy for character-wise breaking.
        if ($hasOversizeWord) {
            return $this->greedyFallback($paragraph);
        }

        $spaceWidth = $this->measurer->widthPt(' ');
        $stretchPerSpace = $spaceWidth * $this->stretchRatio;
        $shrinkPerSpace = $spaceWidth * $this->shrinkRatio;

        // f[i] = min demerits to break at position i (i ∈ [0, n]).
        // i=0 → no breaks yet; i=n → paragraph end.
        $f = array_fill(0, $n + 1, INF);
        $f[0] = 0.0;
        $trail = array_fill(0, $n + 1, -1);

        // Prefix sums for fast boxWidth computation.
        $prefixWidth = [0.0];
        $sum = 0.0;
        foreach ($widths as $w) {
            $sum += $w;
            $prefixWidth[] = $sum;
        }

        for ($i = 1; $i <= $n; $i++) {
            $isLast = ($i === $n);
            for ($j = 0; $j < $i; $j++) {
                if ($f[$j] === INF) {
                    continue;
                }
                $numWords = $i - $j;
                $numSpaces = $numWords - 1;
                $boxWidth = $prefixWidth[$i] - $prefixWidth[$j];
                $natural = $boxWidth + $numSpaces * $spaceWidth;

                // Compute adjustment ratio r.
                $r = $this->adjustmentRatio(
                    $natural,
                    $numSpaces,
                    $stretchPerSpace,
                    $shrinkPerSpace,
                    $isLast,
                );

                if ($r === null) {
                    continue; // infeasible — line cannot fit.
                }

                $badness = 100.0 * pow(abs($r), 3);
                $demerits = pow(1.0 + $badness, 2);
                $total = $f[$j] + $demerits;

                if ($total < $f[$i]) {
                    $f[$i] = $total;
                    $trail[$i] = $j;
                }
            }
        }

        if ($f[$n] === INF) {
            // No feasible break-set — fallback to greedy.
            return $this->greedyFallback($paragraph);
        }

        // Reconstruct breaks.
        $breaks = [];
        $cur = $n;
        while ($cur > 0) {
            $breaks[] = $cur;
            $cur = $trail[$cur];
            if ($cur < 0) {
                break;
            }
        }
        $breaks = array_reverse($breaks);

        // Build lines.
        $lines = [];
        $start = 0;
        foreach ($breaks as $end) {
            $lineWords = array_slice($words, $start, $end - $start);
            $lines[] = implode(' ', $lineWords);
            $start = $end;
        }

        return $lines;
    }

    /**
     * Compute Knuth-Plass adjustment ratio for a line with natural width $L,
     * $numSpaces glue items with per-space stretch/shrink, and a flag for
     * whether this is the last paragraph line. Returns null if infeasible
     * (too wide cannot shrink, too narrow cannot stretch beyond ratio>10).
     */
    private function adjustmentRatio(
        float $natural,
        int $numSpaces,
        float $stretchPerSpace,
        float $shrinkPerSpace,
        bool $isLast,
    ): ?float {
        if (abs($natural - $this->maxWidthPt) < 1e-6) {
            return 0.0;
        }

        if ($natural > $this->maxWidthPt) {
            // Need shrink — negative r.
            if ($numSpaces === 0) {
                return null; // single word, cannot shrink.
            }
            $shrink = $numSpaces * $shrinkPerSpace;
            if ($shrink < 1e-9) {
                return null;
            }
            $r = ($natural - $this->maxWidthPt) / $shrink;
            if ($r > 1.0) {
                return null; // beyond shrink limit.
            }

            return -$r;
        }

        // Natural < maxWidth — stretch positive r.
        if ($isLast) {
            // Last line — no stretch penalty (ragged-right OK).
            return 0.0;
        }
        if ($numSpaces === 0) {
            return 0.0; // single word — no stretch needed.
        }
        $stretch = $numSpaces * $stretchPerSpace;
        if ($stretch < 1e-9) {
            return null;
        }
        $r = ($this->maxWidthPt - $natural) / $stretch;
        if ($r > 10.0) {
            return null; // beyond stretch limit.
        }

        return $r;
    }

    /**
     * @return list<string>
     */
    private function greedyFallback(string $paragraph): array
    {
        $greedy = new LineBreaker($this->measurer, $this->maxWidthPt);

        return $greedy->wrap($paragraph);
    }
}
