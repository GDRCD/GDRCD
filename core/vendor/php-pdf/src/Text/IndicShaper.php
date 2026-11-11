<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Text;

/**
 * Indic script basic shaping — pre-base matra reordering.
 *
 * In Indic scripts (Devanagari, Bengali, Tamil, Telugu, Kannada,
 * Malayalam, Gujarati, Gurmukhi, Oriya, Sinhala), certain vowel signs
 * (matras) appear visually BEFORE the base consonant despite being
 * stored AFTER the consonant in logical order.
 *
 * Example (Devanagari):
 *   "कि" = क (U+0915) + ि (U+093F SHORT_I matra)
 *   Logical order: क, ि
 *   Visual order:  ि, क  (matra displayed to the left of the consonant)
 *
 * Without reordering, "किताब" (kitab, book) renders as "क ि त ा ब" —
 * matra after base instead of before.
 *
 * Per OpenType USE (Universal Shaping Engine), reordering rules:
 *  1. Identify syllable boundary
 *  2. Find pre-base matra in syllable
 *  3. Move it to position immediately before the syllable's base consonant
 *     (skipping any preceding RA+Halant or virama-stacked components)
 *
 * Scope:
 *  - Single-character pre-base matras (most common)
 *  - Two-part matras decomposition (Bengali ো → ে + া, Tamil ொ, Malayalam
 *    ൊ, Oriya ୋ, Kannada ೋ, Sinhala ො — 18 entries across 6 scripts)
 *  - Reph reordering: if syllable starts with RA + Halant + Consonant, move
 *    RA + Halant to end of syllable (USE intermediate output). Visual reph
 *    glyph substitution and mark positioning still need font GSUB+GPOS.
 *  - 11 Indic scripts: Devanagari, Bengali, Gurmukhi, Gujarati, Oriya,
 *    Tamil, Telugu, Kannada, Malayalam, Sinhala
 *
 * Not implemented:
 *  - GSUB 'rphf' application: trailing RA+halant → single reph mark glyph
 *    (needs Type 1 / Type 6 substitution support in GsubReader)
 *  - GPOS mark-to-base positioning for reph
 *  - Conjunct ligatures (need GSUB ccmp / akhn / blwf features)
 *  - Above/below-base matra positioning (mostly handled by font itself)
 *
 * Sinhala U+0DDA, U+0DDD clarification:
 *  Their NFD decomposition includes virama (U+0DCA), which in the middle of
 *  a matra sequence would break syllable-end detection in reph reorder.
 *  Therefore they are NOT added to TWO_PART_MATRAS — they render as
 *  single-codepoint pre-base matras (already in PRE_BASE_MATRAS). Most
 *  Sinhala fonts support U+0DDA/U+0DDD directly as a single glyph; for
 *  fonts without support, the caller must decompose before passing (rare).
 */
final class IndicShaper
{
    /**
     * Pre-base matras for each Indic script.
     * Single-codepoint matras only (no two-part decomposition).
     */
    private const PRE_BASE_MATRAS = [
        // Devanagari
        0x093F => true,  // ि DEVANAGARI VOWEL SIGN I
        // Bengali
        0x09BF => true,  // ি BENGALI VOWEL SIGN I
        0x09C7 => true,  // ে BENGALI VOWEL SIGN E
        0x09C8 => true,  // ৈ BENGALI VOWEL SIGN AI
        // Gurmukhi
        0x0A3F => true,  // ਿ GURMUKHI VOWEL SIGN I
        // Gujarati
        0x0ABF => true,  // િ GUJARATI VOWEL SIGN I
        // Oriya
        0x0B47 => true,  // େ ORIYA VOWEL SIGN E
        // Tamil
        0x0BC6 => true,  // ெ TAMIL VOWEL SIGN E
        0x0BC7 => true,  // ே TAMIL VOWEL SIGN EE
        0x0BC8 => true,  // ை TAMIL VOWEL SIGN AI
        // Telugu
        0x0C46 => true,  // ె TELUGU VOWEL SIGN E
        0x0C47 => true,  // ే TELUGU VOWEL SIGN EE
        0x0C48 => true,  // ై TELUGU VOWEL SIGN AI
        // Kannada
        0x0CC6 => true,  // ೆ KANNADA VOWEL SIGN E
        // Malayalam
        0x0D46 => true,  // െ MALAYALAM VOWEL SIGN E
        0x0D47 => true,  // േ MALAYALAM VOWEL SIGN EE
        0x0D48 => true,  // ൈ MALAYALAM VOWEL SIGN AI
        // Sinhala
        0x0DD9 => true,  // ෙ SINHALA VOWEL SIGN KOMBUVA
        0x0DDA => true,  // ේ SINHALA VOWEL SIGN DIGA KOMBUVA
        0x0DDB => true,  // ෛ SINHALA VOWEL SIGN KOMBU DEKA
    ];

    /**
     * Consonant ranges per script — used to find base consonant in syllable
     * walk-back from pre-base matra.
     * Per Unicode Indic blocks (rough approximation).
     */
    private const CONSONANT_RANGES = [
        [0x0915, 0x0939],  // Devanagari consonants क..ह
        [0x0958, 0x095F],  // Devanagari additional consonants
        [0x0978, 0x097F],
        [0x0995, 0x09B9],  // Bengali
        [0x09DC, 0x09DF],
        [0x0A15, 0x0A39],  // Gurmukhi
        [0x0A59, 0x0A5E],
        [0x0A95, 0x0AB9],  // Gujarati
        [0x0B15, 0x0B39],  // Oriya
        [0x0B5C, 0x0B5F],
        [0x0B95, 0x0BB9],  // Tamil
        [0x0C15, 0x0C39],  // Telugu
        [0x0C58, 0x0C5A],
        [0x0C95, 0x0CB9],  // Kannada
        [0x0CDE, 0x0CDE],
        [0x0D15, 0x0D3A],  // Malayalam
        [0x0D54, 0x0D56],
        [0x0D5F, 0x0D61],
        [0x0D9A, 0x0DC6],  // Sinhala
    ];

    /**
     * RA characters per script — first element of reph cluster (RA + Halant).
     * When syllable starts with RA + virama + consonant, RA+virama becomes reph.
     */
    private const REPH_RA = [
        0x0930 => true,  // Devanagari र
        0x09B0 => true,  // Bengali র
        0x09F0 => true,  // Bengali alternative ৰ (Assamese)
        0x0A30 => true,  // Gurmukhi ਰ
        0x0AB0 => true,  // Gujarati ર
        0x0B30 => true,  // Oriya ର
        0x0BB0 => true,  // Tamil ர
        0x0C30 => true,  // Telugu ర
        0x0CB0 => true,  // Kannada ರ
        0x0D30 => true,  // Malayalam ര
        0x0DBB => true,  // Sinhala ර
    ];

    /**
     * Syllable mark ranges — combining marks that extend a syllable past its
     * base consonant. Includes matras (vowel signs), anusvara, visarga, nukta,
     * stress marks, and length marks. Used by reph reorder to find syllable end.
     */
    private const SYLLABLE_MARK_RANGES = [
        [0x0900, 0x0903],   // Devanagari signs (inverted candrabindu, candrabindu, anusvara, visarga)
        [0x093A, 0x094F],   // Devanagari matras (incl. virama 094D handled separately)
        [0x0951, 0x0957],   // Devanagari stress/cantillation
        [0x0962, 0x0963],   // Devanagari additional vowel signs
        [0x0981, 0x0983],   // Bengali signs
        [0x09BC, 0x09CC],   // Bengali nukta + matras
        [0x09D7, 0x09D7],   // Bengali AU-length mark
        [0x09E2, 0x09E3],
        [0x0A01, 0x0A03],   // Gurmukhi signs
        [0x0A3C, 0x0A4C],   // Gurmukhi nukta + matras
        [0x0A70, 0x0A71],
        [0x0A81, 0x0A83],   // Gujarati signs
        [0x0ABC, 0x0ACC],
        [0x0AE2, 0x0AE3],
        [0x0B01, 0x0B03],   // Oriya
        [0x0B3C, 0x0B4C],
        [0x0B55, 0x0B57],
        [0x0B62, 0x0B63],
        [0x0BBE, 0x0BCC],   // Tamil matras
        [0x0BD7, 0x0BD7],
        [0x0C00, 0x0C03],   // Telugu
        [0x0C3C, 0x0C4C],
        [0x0C55, 0x0C56],
        [0x0C62, 0x0C63],
        [0x0C81, 0x0C83],   // Kannada
        [0x0CBC, 0x0CCC],
        [0x0CD5, 0x0CD6],
        [0x0CE2, 0x0CE3],
        [0x0D00, 0x0D03],   // Malayalam
        [0x0D3B, 0x0D4C],
        [0x0D57, 0x0D57],
        [0x0D62, 0x0D63],
        [0x0D81, 0x0D83],   // Sinhala
        [0x0DCF, 0x0DDF],
        [0x0DF2, 0x0DF3],
    ];

    /**
     * Two-part matras — single codepoints that canonically
     * decompose into a sequence of constituent matras. Per OpenType USE,
     * decomposition must run BEFORE reordering, so that pre-base components
     * can be moved independently from post-base parts.
     *
     * Example (Bengali):
     *   ো U+09CB BENGALI VOWEL SIGN O = U+09C7 (pre-base e) + U+09BE (post-base aa)
     *
     * Decomposition table matches Unicode canonical (NFD) decompositions for
     * Indic two/three-part vowel signs.
     *
     * @var array<int, list<int>>
     */
    private const TWO_PART_MATRAS = [
        // Bengali
        0x09CB => [0x09C7, 0x09BE],  // ো = ে + া
        0x09CC => [0x09C7, 0x09D7],  // ৌ = ে + ৗ
        // Oriya
        0x0B48 => [0x0B47, 0x0B56],  // ୈ = େ + ୖ
        0x0B4B => [0x0B47, 0x0B3E],  // ୋ = େ + ା
        0x0B4C => [0x0B47, 0x0B57],  // ୌ = େ + ୗ
        // Tamil
        0x0BCA => [0x0BC6, 0x0BBE],  // ொ = ெ + ா
        0x0BCB => [0x0BC7, 0x0BBE],  // ோ = ே + ா
        0x0BCC => [0x0BC6, 0x0BD7],  // ௌ = ெ + ௗ
        // Malayalam
        0x0D4A => [0x0D46, 0x0D3E],  // ൊ = െ + ാ
        0x0D4B => [0x0D47, 0x0D3E],  // ോ = േ + ാ
        0x0D4C => [0x0D46, 0x0D57],  // ൌ = െ + ൗ
        // Kannada (three-part for U+0CCB)
        0x0CC0 => [0x0CBF, 0x0CD5],  // ೀ = ಿ + ೕ
        0x0CC7 => [0x0CC6, 0x0CD5],  // ೇ = ೆ + ೕ
        0x0CC8 => [0x0CC6, 0x0CD6],  // ೈ = ೆ + ೖ
        0x0CCA => [0x0CC6, 0x0CC2],  // ೊ = ೆ + ು
        0x0CCB => [0x0CC6, 0x0CC2, 0x0CD5],  // ೋ = ೆ + ು + ೕ
        // Sinhala — only entries without a virama component (others would break
        // syllable-end detection since virama acts as a conjunct marker).
        0x0DDC => [0x0DD9, 0x0DCF],  // ො = ෙ + ා
        0x0DDE => [0x0DD9, 0x0DDF],  // ෞ = ෙ + ෟ
    ];

    /**
     * Virama (halant) codepoints per script.
     */
    private const VIRAMAS = [
        0x094D => true,  // Devanagari
        0x09CD => true,  // Bengali
        0x0A4D => true,  // Gurmukhi
        0x0ACD => true,  // Gujarati
        0x0B4D => true,  // Oriya
        0x0BCD => true,  // Tamil
        0x0C4D => true,  // Telugu
        0x0CCD => true,  // Kannada
        0x0D4D => true,  // Malayalam
        0x0DCA => true,  // Sinhala
    ];

    /**
     * Apply Indic shaping to codepoint list. Pipeline:
     *  1. Decompose two-part matras (Bengali ো → ে + া, etc.) per Unicode NFD.
     *  2. Reph reorder: RA + Halant at syllable start → moved to end of
     *     syllable (after base + matras + conjunct halant-cons pairs).
     *  3. Pre-base matra reorder: pre-base matras moved before base consonant.
     *
     * @param  list<int>  $cps
     * @return list<int>
     */
    public static function shape(array $cps): array
    {
        return self::reorderPreBaseMatras(self::reorderReph(self::decomposeMatras($cps)));
    }

    /**
     * Decompose two/three-part matras into their constituent
     * components. After this step, pre-base components can be moved
     * independently from post-base components by the matra reorder pass.
     *
     * @param  list<int>  $cps
     * @return list<int>
     */
    private static function decomposeMatras(array $cps): array
    {
        $hasTwoPart = false;
        foreach ($cps as $cp) {
            if (isset(self::TWO_PART_MATRAS[$cp])) {
                $hasTwoPart = true;
                break;
            }
        }
        if (! $hasTwoPart) {
            return $cps;
        }
        $out = [];
        foreach ($cps as $cp) {
            if (isset(self::TWO_PART_MATRAS[$cp])) {
                foreach (self::TWO_PART_MATRAS[$cp] as $part) {
                    $out[] = $part;
                }
            } else {
                $out[] = $cp;
            }
        }

        return $out;
    }

    /**
     * Reph reorder.
     *
     * If syllable starts with RA + virama + consonant, the RA+virama pair is
     * treated as "reph" candidate. Per OpenType USE, reph is logically moved
     * to end of syllable (the GSUB 'rphf' feature then substitutes it with
     * reph mark glyph, and GPOS positions the mark above base).
     *
     * Without GSUB+GPOS we cannot render visual reph as a mark above the base,
     * but reordering matches the canonical USE intermediate state and is
     * necessary for fonts that DO apply rphf.
     *
     * Syllable extent: base consonant + following halant-consonant pairs +
     * syllable marks (matras, nukta, anusvara, visarga). Terminates at next
     * non-mark codepoint (whitespace, punctuation, ASCII, independent vowel,
     * another base consonant without halant).
     *
     * @param  list<int>  $cps
     * @return list<int>
     */
    private static function reorderReph(array $cps): array
    {
        $n = count($cps);
        $out = [];
        $i = 0;
        while ($i < $n) {
            if (isset(self::REPH_RA[$cps[$i]])
                && $i + 2 < $n
                && isset(self::VIRAMAS[$cps[$i + 1]])
                && self::isConsonant($cps[$i + 2])
            ) {
                $ra = $cps[$i];
                $halant = $cps[$i + 1];
                $baseIdx = $i + 2;
                $end = $baseIdx + 1;
                while ($end < $n) {
                    $cp = $cps[$end];
                    if (isset(self::VIRAMAS[$cp])
                        && $end + 1 < $n
                        && self::isConsonant($cps[$end + 1])
                    ) {
                        $end += 2;
                    } elseif (self::isSyllableMark($cp)) {
                        $end++;
                    } else {
                        break;
                    }
                }
                for ($k = $baseIdx; $k < $end; $k++) {
                    $out[] = $cps[$k];
                }
                $out[] = $ra;
                $out[] = $halant;
                $i = $end;
            } else {
                $out[] = $cps[$i];
                $i++;
            }
        }

        return $out;
    }

    /**
     * Pre-base matra reorder. For each pre-base matra at position i:
     * walk backward through halant-consonant pairs to find syllable start;
     * insert matra immediately before syllable start.
     *
     * @param  list<int>  $cps
     * @return list<int>
     */
    private static function reorderPreBaseMatras(array $cps): array
    {
        $out = $cps;
        $n = count($out);
        $i = 0;
        while ($i < $n) {
            $cp = $out[$i];
            if (! isset(self::PRE_BASE_MATRAS[$cp])) {
                $i++;

                continue;
            }
            // Walk backward to find syllable start (base consonant).
            $start = $i - 1;
            if ($start < 0 || ! self::isConsonant($out[$start])) {
                $i++;

                continue;
            }
            // Step further back through halant + consonant pairs.
            while ($start - 2 >= 0
                && isset(self::VIRAMAS[$out[$start - 1]])
                && self::isConsonant($out[$start - 2])) {
                $start -= 2;
            }
            // Move matra from $i to position $start.
            if ($start === $i - 1) {
                // Simple case: swap matra with immediately preceding consonant.
                [$out[$start], $out[$i]] = [$out[$i], $out[$start]];
            } else {
                // Multi-step: remove matra from $i, insert at $start.
                $matra = $out[$i];
                array_splice($out, $i, 1);
                array_splice($out, $start, 0, [$matra]);
            }
            $i++; // matra now at $start; advance past where it was
        }

        return $out;
    }

    /**
     * UTF-8 → reordered codepoints.
     *
     * @return list<int>
     */
    public static function shapeUtf8(string $utf8): array
    {
        return self::shape(self::utf8ToCps($utf8));
    }

    public static function isPreBaseMatra(int $cp): bool
    {
        return isset(self::PRE_BASE_MATRAS[$cp]);
    }

    public static function isVirama(int $cp): bool
    {
        return isset(self::VIRAMAS[$cp]);
    }

    public static function isConsonant(int $cp): bool
    {
        foreach (self::CONSONANT_RANGES as [$lo, $hi]) {
            if ($cp >= $lo && $cp <= $hi) {
                return true;
            }
        }

        return false;
    }

    public static function isRA(int $cp): bool
    {
        return isset(self::REPH_RA[$cp]);
    }

    public static function isTwoPartMatra(int $cp): bool
    {
        return isset(self::TWO_PART_MATRAS[$cp]);
    }

    /**
     * Returns decomposition components for a two-part matra, or [$cp] if not.
     *
     * @return list<int>
     */
    public static function decomposeMatra(int $cp): array
    {
        return self::TWO_PART_MATRAS[$cp] ?? [$cp];
    }

    public static function isSyllableMark(int $cp): bool
    {
        foreach (self::SYLLABLE_MARK_RANGES as [$lo, $hi]) {
            if ($cp >= $lo && $cp <= $hi) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<int>
     */
    public static function utf8ToCps(string $utf8): array
    {
        $cps = [];
        $i = 0;
        $len = strlen($utf8);
        while ($i < $len) {
            $b = ord($utf8[$i]);
            if ($b < 0x80) {
                $cps[] = $b;
                $i++;
            } elseif (($b & 0xE0) === 0xC0) {
                $cps[] = (($b & 0x1F) << 6) | (ord($utf8[$i + 1]) & 0x3F);
                $i += 2;
            } elseif (($b & 0xF0) === 0xE0) {
                $cps[] = (($b & 0x0F) << 12) | ((ord($utf8[$i + 1]) & 0x3F) << 6) | (ord($utf8[$i + 2]) & 0x3F);
                $i += 3;
            } else {
                $cps[] = (($b & 0x07) << 18) | ((ord($utf8[$i + 1]) & 0x3F) << 12)
                    | ((ord($utf8[$i + 2]) & 0x3F) << 6) | (ord($utf8[$i + 3]) & 0x3F);
                $i += 4;
            }
        }

        return $cps;
    }
}
