<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Layout;

/**
 * Greedy line-breaking algorithm.
 *
 * Algorithm:
 *   1. Split text into "words" (by whitespace, preserving any
 *      explicit `\n` as a hard break).
 *   2. Iterate word-by-word. If current_line + " " + word fits within
 *      maxWidthPt — append. Otherwise break, start a new line with word.
 *   3. A word wider than maxWidthPt — character-wise break (URLs, long
 *      identifiers).
 *
 * Returns list<string> — each entry is one line to render.
 *
 * Not implemented here:
 *  - Knuth-Plass optimal (requires backtracking + boxes-glues-penalties)
 *  - Hanging punctuation
 *  - Tab-stops
 *
 * Handled elsewhere:
 *  - Soft-hyphen (U+00AD) handling
 *  - Justification (text-align: justify)
 *  - Hyphenation (basic syllable rules)
 *
 * Matches the ADR choice "typography v0.1: kerning + basic ligatures
 * only; line breaking — greedy".
 */
final class LineBreaker
{
    public function __construct(
        private readonly TextMeasurer $measurer,
        private readonly float $maxWidthPt,
    ) {}

    /**
     * @return list<string>
     */
    public function wrap(string $text): array
    {
        $lines = [];
        // Honor explicit newlines — each "\n" = hard break.
        foreach (explode("\n", $text) as $paragraph) {
            $wrapped = $this->wrapParagraph($paragraph);
            foreach ($wrapped as $line) {
                $lines[] = $line;
            }
            // Each paragraph ends with a line — even if the last
            // line is non-empty. An empty paragraph emits one empty line
            // (= blank line in output).
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
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;
            if ($this->measurer->widthPt($candidate) <= $this->maxWidthPt) {
                $current = $candidate;

                continue;
            }
            // Word does not fit alongside the previous content.
            if ($current !== '') {
                $lines[] = $current;
                $current = '';
            }
            // If the word itself is wider than maxWidth — character-wise break.
            if ($this->measurer->widthPt($word) > $this->maxWidthPt) {
                $broken = $this->breakLongWord($word);
                // Last fragment stays in current; the rest are emitted
                // immediately as finished lines.
                $last = array_pop($broken);
                foreach ($broken as $piece) {
                    $lines[] = $piece;
                }
                $current = $last ?? '';
            } else {
                $current = $word;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    /**
     * Splits a word wider than maxWidthPt into pieces character-by-
     * character. Used for URLs / long identifiers.
     *
     * @return list<string>
     */
    private function breakLongWord(string $word): array
    {
        $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $pieces = [];
        $current = '';
        foreach ($chars as $ch) {
            $candidate = $current.$ch;
            if ($this->measurer->widthPt($candidate) <= $this->maxWidthPt) {
                $current = $candidate;

                continue;
            }
            if ($current !== '') {
                $pieces[] = $current;
            }
            $current = $ch;
        }
        if ($current !== '') {
            $pieces[] = $current;
        }

        return $pieces;
    }
}
