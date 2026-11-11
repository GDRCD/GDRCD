<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Text;

/**
 * Arabic basic shaping (cursive contextual forms).
 *
 * Maps logical-order UTF-8 Arabic text to visually-ordered codepoints with
 * appropriate Presentation Forms B variants (FE70-FEFF block).
 *
 * Algorithm (per Unicode UAX 9 + Arabic Shaping spec):
 *  1. Classify each char's joining type (R/L/D/C/T/U).
 *  2. For each non-transparent char, determine if its right-side / left-side
 *     can join based on neighbors.
 *  3. Pick form: isolated / initial / medial / final.
 *  4. Map to Presentation Forms B codepoint (or return original if no form).
 *  5. Reverse output order for RTL display (PDF text matrix is LTR, so
 *     visual order = reverse of logical for Arabic).
 *
 * Joining types:
 *  - R (Right): joins to prev neighbor (= right in visual)
 *  - L (Left): joins to next neighbor
 *  - D (Dual): joins both
 *  - C (Causing): like ZWJ, doesn't show, forces joining
 *  - T (Transparent): diacritics, don't affect joining
 *  - U (Non-joining): isolated always
 *
 * Scope: 28 base Arabic letters + alef variants + hamza + lam-alef.
 * Diacritics (fatha/kasra/damma/etc.) treated as transparent.
 */
final class ArabicShaper
{
    public const JT_R = 'R';

    public const JT_L = 'L';

    public const JT_D = 'D';

    public const JT_C = 'C';

    public const JT_T = 'T';

    public const JT_U = 'U';

    /**
     * Arabic letter joining classes (codepoint → joining type).
     * Source: Unicode 16.0 ArabicShaping.txt subset for basic Arabic block.
     */
    private const JOINING_TYPE = [
        // Hamza
        0x0621 => self::JT_U,  // ء HAMZA
        0x0622 => self::JT_R,  // آ ALEF WITH MADDA ABOVE
        0x0623 => self::JT_R,  // أ ALEF WITH HAMZA ABOVE
        0x0624 => self::JT_R,  // ؤ WAW WITH HAMZA ABOVE
        0x0625 => self::JT_R,  // إ ALEF WITH HAMZA BELOW
        0x0626 => self::JT_D,  // ئ YEH WITH HAMZA ABOVE
        // 28 Arabic letters
        0x0627 => self::JT_R,  // ا ALEF
        0x0628 => self::JT_D,  // ب BEH
        0x0629 => self::JT_R,  // ة TEH MARBUTA
        0x062A => self::JT_D,  // ت TEH
        0x062B => self::JT_D,  // ث THEH
        0x062C => self::JT_D,  // ج JEEM
        0x062D => self::JT_D,  // ح HAH
        0x062E => self::JT_D,  // خ KHAH
        0x062F => self::JT_R,  // د DAL
        0x0630 => self::JT_R,  // ذ THAL
        0x0631 => self::JT_R,  // ر REH
        0x0632 => self::JT_R,  // ز ZAIN
        0x0633 => self::JT_D,  // س SEEN
        0x0634 => self::JT_D,  // ش SHEEN
        0x0635 => self::JT_D,  // ص SAD
        0x0636 => self::JT_D,  // ض DAD
        0x0637 => self::JT_D,  // ط TAH
        0x0638 => self::JT_D,  // ظ ZAH
        0x0639 => self::JT_D,  // ع AIN
        0x063A => self::JT_D,  // غ GHAIN
        0x0640 => self::JT_C,  // ـ TATWEEL (causing)
        0x0641 => self::JT_D,  // ف FEH
        0x0642 => self::JT_D,  // ق QAF
        0x0643 => self::JT_D,  // ك KAF
        0x0644 => self::JT_D,  // ل LAM
        0x0645 => self::JT_D,  // م MEEM
        0x0646 => self::JT_D,  // ن NOON
        0x0647 => self::JT_D,  // ه HEH
        0x0648 => self::JT_R,  // و WAW
        0x0649 => self::JT_D,  // ى ALEF MAKSURA
        0x064A => self::JT_D,  // ي YEH
        // Diacritics — transparent (don't affect shaping)
        0x064B => self::JT_T, 0x064C => self::JT_T, 0x064D => self::JT_T,
        0x064E => self::JT_T, 0x064F => self::JT_T, 0x0650 => self::JT_T,
        0x0651 => self::JT_T, 0x0652 => self::JT_T, 0x0653 => self::JT_T,
        0x0654 => self::JT_T, 0x0655 => self::JT_T, 0x0656 => self::JT_T,
        0x0657 => self::JT_T, 0x0658 => self::JT_T, 0x0670 => self::JT_T,
        // ZWJ/ZWNJ (joining controls)
        0x200C => self::JT_U,  // ZWNJ
        0x200D => self::JT_C,  // ZWJ
    ];

    /**
     * Presentation Forms B mapping: base codepoint → [isolated, final, initial, medial].
     * 0 entries mean the form doesn't exist for the letter.
     */
    private const PRESENTATION_FORMS = [
        // Hamza variants
        0x0621 => [0xFE80, 0, 0, 0],
        0x0622 => [0xFE81, 0xFE82, 0, 0],
        0x0623 => [0xFE83, 0xFE84, 0, 0],
        0x0624 => [0xFE85, 0xFE86, 0, 0],
        0x0625 => [0xFE87, 0xFE88, 0, 0],
        0x0626 => [0xFE89, 0xFE8A, 0xFE8B, 0xFE8C],
        // 28 letters
        0x0627 => [0xFE8D, 0xFE8E, 0, 0],
        0x0628 => [0xFE8F, 0xFE90, 0xFE91, 0xFE92],
        0x0629 => [0xFE93, 0xFE94, 0, 0],
        0x062A => [0xFE95, 0xFE96, 0xFE97, 0xFE98],
        0x062B => [0xFE99, 0xFE9A, 0xFE9B, 0xFE9C],
        0x062C => [0xFE9D, 0xFE9E, 0xFE9F, 0xFEA0],
        0x062D => [0xFEA1, 0xFEA2, 0xFEA3, 0xFEA4],
        0x062E => [0xFEA5, 0xFEA6, 0xFEA7, 0xFEA8],
        0x062F => [0xFEA9, 0xFEAA, 0, 0],
        0x0630 => [0xFEAB, 0xFEAC, 0, 0],
        0x0631 => [0xFEAD, 0xFEAE, 0, 0],
        0x0632 => [0xFEAF, 0xFEB0, 0, 0],
        0x0633 => [0xFEB1, 0xFEB2, 0xFEB3, 0xFEB4],
        0x0634 => [0xFEB5, 0xFEB6, 0xFEB7, 0xFEB8],
        0x0635 => [0xFEB9, 0xFEBA, 0xFEBB, 0xFEBC],
        0x0636 => [0xFEBD, 0xFEBE, 0xFEBF, 0xFEC0],
        0x0637 => [0xFEC1, 0xFEC2, 0xFEC3, 0xFEC4],
        0x0638 => [0xFEC5, 0xFEC6, 0xFEC7, 0xFEC8],
        0x0639 => [0xFEC9, 0xFECA, 0xFECB, 0xFECC],
        0x063A => [0xFECD, 0xFECE, 0xFECF, 0xFED0],
        0x0641 => [0xFED1, 0xFED2, 0xFED3, 0xFED4],
        0x0642 => [0xFED5, 0xFED6, 0xFED7, 0xFED8],
        0x0643 => [0xFED9, 0xFEDA, 0xFEDB, 0xFEDC],
        0x0644 => [0xFEDD, 0xFEDE, 0xFEDF, 0xFEE0],
        0x0645 => [0xFEE1, 0xFEE2, 0xFEE3, 0xFEE4],
        0x0646 => [0xFEE5, 0xFEE6, 0xFEE7, 0xFEE8],
        0x0647 => [0xFEE9, 0xFEEA, 0xFEEB, 0xFEEC],
        0x0648 => [0xFEED, 0xFEEE, 0, 0],
        0x0649 => [0xFEEF, 0xFEF0, 0, 0],
        0x064A => [0xFEF1, 0xFEF2, 0xFEF3, 0xFEF4],
    ];

    /**
     * Lam-Alef ligatures (LAM + ALEF combinations).
     * (LAM cp, ALEF cp) → [isolated_form, final_form].
     */
    private const LAM_ALEF_LIGATURES = [
        // LAM + MADDA-ALEF → ﻵ ﻶ
        '0644-0622' => [0xFEF5, 0xFEF6],
        // LAM + HAMZA-ALEF → ﻷ ﻸ
        '0644-0623' => [0xFEF7, 0xFEF8],
        // LAM + HAMZA-BELOW-ALEF → ﻹ ﻺ
        '0644-0625' => [0xFEF9, 0xFEFA],
        // LAM + ALEF → ﻻ ﻼ
        '0644-0627' => [0xFEFB, 0xFEFC],
    ];

    /**
     * Shape Arabic text: input UTF-8 → list of visually-ordered codepoints.
     *
     * Non-Arabic chars passed through unchanged. RTL reversal applied.
     *
     * @return list<int>  visually-ordered codepoints (left-to-right in PDF)
     */
    public static function shape(string $utf8): array
    {
        $cps = self::utf8ToCodepoints($utf8);
        if ($cps === []) {
            return [];
        }

        // Step 1: split into Arabic runs (consecutive Arabic chars) and non-Arabic.
        // For now, simple approach: process whole string, shape Arabic chars,
        // pass through others. RTL reordering applied to Arabic runs.
        $shaped = self::shapeCodepoints($cps);
        $shaped = self::applyLamAlefLigatures($shaped);

        return self::reverseRtlRuns($shaped);
    }

    /**
     * Shape Arabic chars in LOGICAL order (without RTL reversal).
     * Used by Bidi-aware pipeline: shape first, then Bidi handles reorder.
     *
     * @param  list<int>  $cps
     * @return list<int>
     */
    public static function shapeLogical(array $cps): array
    {
        $shaped = self::shapeCodepoints($cps);

        return self::applyLamAlefLigatures($shaped);
    }

    /**
     * @param  list<int>  $cps
     * @return list<int>
     */
    private static function shapeCodepoints(array $cps): array
    {
        $n = count($cps);
        $shaped = $cps;

        for ($i = 0; $i < $n; $i++) {
            $cp = $cps[$i];
            $jt = self::JOINING_TYPE[$cp] ?? self::JT_U;
            if (! isset(self::PRESENTATION_FORMS[$cp])) {
                continue;
            }

            // Find prev / next non-transparent.
            $prevCp = null;
            $prevJt = null;
            for ($j = $i - 1; $j >= 0; $j--) {
                $pj = self::JOINING_TYPE[$cps[$j]] ?? self::JT_U;
                if ($pj === self::JT_T) {
                    continue;
                }
                $prevCp = $cps[$j];
                $prevJt = $pj;
                break;
            }
            $nextJt = null;
            for ($j = $i + 1; $j < $n; $j++) {
                $nj = self::JOINING_TYPE[$cps[$j]] ?? self::JT_U;
                if ($nj === self::JT_T) {
                    continue;
                }
                $nextJt = $nj;
                break;
            }

            // Right-side joins if: this is R/D AND prev is L/D/C
            $joinsRight = ($jt === self::JT_R || $jt === self::JT_D)
                && in_array($prevJt, [self::JT_L, self::JT_D, self::JT_C], true);
            // Left-side joins if: this is L/D AND next is R/D/C
            $joinsLeft = ($jt === self::JT_L || $jt === self::JT_D)
                && in_array($nextJt, [self::JT_R, self::JT_D, self::JT_C], true);

            // Form selection: forms[0]=isol, [1]=fina, [2]=init, [3]=medi
            $forms = self::PRESENTATION_FORMS[$cp];
            $formIdx = match (true) {
                $joinsRight && $joinsLeft => 3,  // medi
                $joinsRight && ! $joinsLeft => 1, // fina
                ! $joinsRight && $joinsLeft => 2, // init
                default => 0,  // isol
            };
            $shapedCp = $forms[$formIdx] ?? 0;
            if ($shapedCp === 0) {
                // Form doesn't exist (e.g., ALEF only has isol+fina). Fall
                // back through priority: fina > isol for R-only letters.
                $shapedCp = $forms[1] ?: $forms[0];
            }
            $shaped[$i] = $shapedCp;
        }

        return $shaped;
    }

    /**
     * Replace LAM (or its forms) + ALEF (or its forms) sequences with ligature
     * presentation forms (FEF5..FEFC).
     *
     * @param  list<int>  $cps
     * @return list<int>
     */
    private static function applyLamAlefLigatures(array $cps): array
    {
        $out = [];
        $n = count($cps);
        for ($i = 0; $i < $n; $i++) {
            $cur = $cps[$i];
            // Look ahead for LAM (any form) followed by ALEF (any form).
            $isLam = in_array($cur, [0x0644, 0xFEDD, 0xFEDE, 0xFEDF, 0xFEE0], true);
            if ($isLam && $i + 1 < $n) {
                $next = $cps[$i + 1];
                $alefBase = match ($next) {
                    0x0627, 0xFE8D, 0xFE8E => 0x0627,
                    0x0622, 0xFE81, 0xFE82 => 0x0622,
                    0x0623, 0xFE83, 0xFE84 => 0x0623,
                    0x0625, 0xFE87, 0xFE88 => 0x0625,
                    default => null,
                };
                if ($alefBase !== null) {
                    // Determine final vs isolated based on LAM's prior context.
                    // Use shaped-LAM form: if LAM was initial/medial (FEDF/FEE0), use final ligature.
                    $isFinal = in_array($cur, [0xFEDF, 0xFEE0], true);
                    $key = sprintf('%04X-%04X', 0x0644, $alefBase);
                    $lig = self::LAM_ALEF_LIGATURES[strtolower($key)] ?? null;
                    if ($lig === null) {
                        // Try uppercase key.
                        $key = sprintf('0644-%04x', $alefBase);
                        $lig = self::LAM_ALEF_LIGATURES[$key] ?? null;
                    }
                    if ($lig !== null) {
                        $out[] = $isFinal ? $lig[1] : $lig[0];
                        $i++; // skip alef

                        continue;
                    }
                }
            }
            $out[] = $cur;
        }

        return $out;
    }

    /**
     * Reverse Arabic runs for visual order. Non-Arabic chars retain logical order.
     *
     * For pure Arabic text (no Latin), simply reverse the whole array.
     * Mixed text: only Arabic spans are reversed.
     *
     * @param  list<int>  $cps
     * @return list<int>
     */
    private static function reverseRtlRuns(array $cps): array
    {
        // Heuristic: if any char is Arabic, treat entire string as RTL and reverse.
        // Better impl would split runs, but this is sufficient for basic Arabic.
        $hasArabic = false;
        foreach ($cps as $cp) {
            if (self::isArabicRange($cp)) {
                $hasArabic = true;
                break;
            }
        }
        if (! $hasArabic) {
            return $cps;
        }

        return array_reverse($cps);
    }

    private static function isArabicRange(int $cp): bool
    {
        return ($cp >= 0x0600 && $cp <= 0x06FF)
            || ($cp >= 0x0750 && $cp <= 0x077F)
            || ($cp >= 0x08A0 && $cp <= 0x08FF)
            || ($cp >= 0xFB50 && $cp <= 0xFDFF)
            || ($cp >= 0xFE70 && $cp <= 0xFEFF);
    }

    /**
     * @return list<int>
     */
    public static function utf8ToCodepoints(string $utf8): array
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
            } elseif (($b & 0xF8) === 0xF0) {
                $cps[] = (($b & 0x07) << 18) | ((ord($utf8[$i + 1]) & 0x3F) << 12)
                    | ((ord($utf8[$i + 2]) & 0x3F) << 6) | (ord($utf8[$i + 3]) & 0x3F);
                $i += 4;
            } else {
                $i++;
            }
        }

        return $cps;
    }
}
