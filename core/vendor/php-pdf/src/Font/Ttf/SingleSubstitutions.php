<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * GSUB Lookup Type 1 (Single Substitution).
 *
 * Single Substitution replaces one glyph with another — the basis for tags
 * 'rphf' (reph), 'half' (half-forms), 'pref' (pre-base forms), 'fina',
 * 'medi', 'init', 'isol' (Arabic/Devanagari positional forms).
 *
 * Subtable formats:
 *   Format 1: coverage + deltaGlyphID → output = input + delta
 *   Format 2: coverage + array of substitute glyph IDs (explicit)
 *
 * Storage structure: gid_in → gid_out (simple map).
 */
final class SingleSubstitutions
{
    /** @var array<int, int> */
    private array $map = [];

    public function add(int $from, int $to): void
    {
        $this->map[$from] = $to;
    }

    public function substitute(int $glyph): int
    {
        return $this->map[$glyph] ?? $glyph;
    }

    public function has(int $glyph): bool
    {
        return isset($this->map[$glyph]);
    }

    /**
     * Apply single substitution to entire glyph list.
     *
     * @param  list<int>  $glyphs
     * @return list<int>
     */
    public function apply(array $glyphs): array
    {
        if ($this->map === []) {
            return $glyphs;
        }
        $out = [];
        foreach ($glyphs as $g) {
            $out[] = $this->map[$g] ?? $g;
        }

        return $out;
    }

    public function isEmpty(): bool
    {
        return $this->map === [];
    }

    public function ruleCount(): int
    {
        return count($this->map);
    }

    /**
     * @return array<int, int>
     */
    public function asArray(): array
    {
        return $this->map;
    }
}
