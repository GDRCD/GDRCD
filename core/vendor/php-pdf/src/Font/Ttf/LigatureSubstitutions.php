<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * Substitution rules for basic ligatures (GSUB lookup type 4).
 *
 * Structure:
 *   firstGlyph → list<LigatureRule>
 *
 * LigatureRule = {components: [g2, g3, ...], result: gN}
 * means: if firstGlyph is followed by g2, g3, ... → replace
 * (firstGlyph + g2 + g3 + ...) with single ligature glyph gN.
 *
 * Multiple rules per firstGlyph (e.g., "f" has rules for "fi", "fl",
 * "ffi", "ffl") — longer rules are sorted first so that "ffi" matches
 * before "fi".
 *
 * Apply algorithm: greedy longest-match per position.
 */
final class LigatureSubstitutions
{
    /** @var array<int, list<array{components: list<int>, result: int, sources: list<int>}>> */
    private array $byFirst = [];

    private int $ruleCount = 0;

    /**
     * @param  list<int>  $components  Glyphs AFTER first (i.e. for "fi"
     *                           pass [i_gid]).
     */
    public function add(int $firstGlyph, array $components, int $resultGlyph): void
    {
        if ($components === []) {
            return;
        }
        $this->byFirst[$firstGlyph][] = [
            'components' => $components,
            'result' => $resultGlyph,
            // 'sources' — array of glyph IDs that compose the ligature
            // (including first). Used by PdfFont to build a
            // multi-codepoint ToUnicode CMap.
            'sources' => array_merge([$firstGlyph], $components),
        ];
        $this->ruleCount++;

        // Sort rules for firstGlyph longest-first.
        usort($this->byFirst[$firstGlyph], static fn ($a, $b) => count($b['components']) - count($a['components']));
    }

    /**
     * Applies ligature substitution to a list of glyph IDs. Greedy
     * longest-match per position. Returns the substituted list plus a map
     * from result to source glyphs (for ToUnicode CMap construction).
     *
     * @param  list<int>  $glyphs
     * @return array{glyphs: list<int>, sourceMap: array<int, list<int>>}
     *         glyphs — after substitution; sourceMap — ligatureGlyph →
     *         list of component glyph IDs (for ToUnicode multi-cp emission)
     */
    public function apply(array $glyphs): array
    {
        if ($this->ruleCount === 0) {
            return ['glyphs' => $glyphs, 'sourceMap' => []];
        }
        $result = [];
        $sourceMap = [];
        $n = count($glyphs);
        $i = 0;
        while ($i < $n) {
            $firstGid = $glyphs[$i];
            $matched = false;
            foreach ($this->byFirst[$firstGid] ?? [] as $rule) {
                $components = $rule['components'];
                $compCount = count($components);
                if ($i + $compCount >= $n) {
                    continue; // not enough glyphs in input
                }
                $allMatch = true;
                for ($k = 0; $k < $compCount; $k++) {
                    if ($glyphs[$i + 1 + $k] !== $components[$k]) {
                        $allMatch = false;
                        break;
                    }
                }
                if (! $allMatch) {
                    continue;
                }
                // Match. Insert ligature glyph, advance $i.
                $result[] = $rule['result'];
                $sourceMap[$rule['result']] = $rule['sources'];
                $i += 1 + $compCount;
                $matched = true;
                break;
            }
            if (! $matched) {
                $result[] = $firstGid;
                $i++;
            }
        }

        return ['glyphs' => $result, 'sourceMap' => $sourceMap];
    }

    public function isEmpty(): bool
    {
        return $this->ruleCount === 0;
    }

    public function ruleCount(): int
    {
        return $this->ruleCount;
    }
}
