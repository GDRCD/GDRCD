<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Text;

/**
 * Unicode Bidirectional Algorithm (UAX #9) — implicit levels.
 *
 * Implements core Bidi rules for mixed LTR/RTL paragraphs:
 *  - Bidi class lookup (range-based, not the full UCD table)
 *  - Paragraph level detection (P2/P3)
 *  - W rules (W1-W7, weak character resolution)
 *  - N rules (N1-N2, neutral resolution)
 *  - I rules (I1-I2, level assignment)
 *  - L2 reordering (reverse RTL runs)
 *
 * Additional features:
 *  - X9 filter: drop bidi formatting characters (LRE/RLE/LRO/RLO/PDF)
 *  - L3 mirroring: paired bracket/punctuation chars in RTL runs swapped
 *    with their Unicode mirror counterparts
 *
 * X1-X10 explicit embedding/override stack (UAX 9 §3.3):
 *  - LRE/RLE push embedding level
 *  - LRO/RLO push level + override (force L or R type)
 *  - PDF pop level
 *  - LRI/RLI/FSI push isolate
 *  - PDI pop isolate
 *  - 125-level max stack depth
 *  - FSI direction = first strong char before matching PDI
 *
 * Not implemented:
 *  - L4 (combining marks reordering) — handled implicitly via NSM class
 *
 * Use case: mixed Latin + Arabic/Hebrew text where the Bidi algorithm
 * is required for correct paragraph layout. Pure unidirectional runs
 * work without this algorithm.
 */
final class BidiAlgorithm
{
    // Strong types
    public const L = 'L';

    public const R = 'R';

    public const AL = 'AL';

    // Weak types
    public const EN = 'EN';

    public const ES = 'ES';

    public const ET = 'ET';

    public const AN = 'AN';

    public const CS = 'CS';

    public const NSM = 'NSM';

    public const BN = 'BN';

    // Neutral types
    public const B = 'B';

    public const S = 'S';

    public const WS = 'WS';

    public const ON = 'ON';

    /**
     * Reorder UTF-8 paragraph for visual display.
     *
     * Returns visually-ordered codepoints (left-to-right). RTL runs reversed.
     *
     * @param  int|null  $paragraphLevel  null = auto-detect (P2/P3); 0 = force LTR; 1 = force RTL
     * @return list<int>
     */
    public static function reorder(string $utf8, ?int $paragraphLevel = null): array
    {
        $cps = self::utf8ToCps($utf8);

        return self::reorderCodepoints($cps, $paragraphLevel);
    }

    /**
     * @param  list<int>  $cps
     * @return list<int>
     */
    public static function reorderCodepoints(array $cps, ?int $paragraphLevel = null): array
    {
        if ($cps === []) {
            return [];
        }
        $types = array_map([self::class, 'bidiClass'], $cps);
        $paragraphLevel ??= self::detectParagraphLevel($types);

        // X1-X8 explicit embedding + override stack processing
        // per UAX 9 §3.3. Sets per-char explicit levels; outputs filtered cps
        // (formatting chars removed by X9) and parallel level array.
        $explicitResult = self::applyExplicitLevels($cps, $types, $paragraphLevel);
        $cps = $explicitResult['cps'];
        $explicitLevels = $explicitResult['levels'];
        $types = $explicitResult['types'];
        if ($cps === []) {
            return [];
        }

        $resolved = $types;
        self::applyW($resolved, $paragraphLevel);
        self::applyN($resolved, $paragraphLevel);
        // Implicit levels (I rules) use base levels = explicit levels
        // from X-step, not paragraph level uniformly.
        $levels = self::applyIWithExplicitBase($resolved, $explicitLevels);

        // L3 mirroring BEFORE L2 reorder — mirror chars at odd
        // (RTL) levels using levels still aligned to original cps.
        $cps = self::applyL3($cps, $levels);

        return self::applyL2($cps, $levels);
    }


    /**
     * L3 mirroring. Applied BEFORE L2 reorder while levels are still
     * parallel to input cps. For each position at odd level (RTL),
     * if the char has a mirror counterpart — replace it.
     *
     * @param  list<int>  $cps
     * @param  list<int>  $levels   levels parallel to input cps
     * @return list<int>
     */
    private static function applyL3(array $cps, array $levels): array
    {
        $out = $cps;
        $n = count($out);
        for ($i = 0; $i < $n; $i++) {
            if (($levels[$i] & 1) === 0) {
                continue; // even level = LTR
            }
            $mirror = self::MIRROR_PAIRS[$out[$i]] ?? null;
            if ($mirror !== null) {
                $out[$i] = $mirror;
            }
        }

        return $out;
    }

    /**
     * BidiMirroring subset — most common ASCII brackets + Unicode brackets.
     *
     * @var array<int, int>
     */
    private const MIRROR_PAIRS = [
        0x0028 => 0x0029,  // ( ↔ )
        0x0029 => 0x0028,
        0x003C => 0x003E,  // < ↔ >
        0x003E => 0x003C,
        0x005B => 0x005D,  // [ ↔ ]
        0x005D => 0x005B,
        0x007B => 0x007D,  // { ↔ }
        0x007D => 0x007B,
        0x00AB => 0x00BB,  // « ↔ »
        0x00BB => 0x00AB,
        0x2039 => 0x203A,  // ‹ ↔ ›
        0x203A => 0x2039,
        0x2045 => 0x2046,  // ⁅ ↔ ⁆
        0x2046 => 0x2045,
        0x2329 => 0x232A,  // 〈 ↔ 〉
        0x232A => 0x2329,
        0x3008 => 0x3009,  // 〈 ↔ 〉
        0x3009 => 0x3008,
        0x300A => 0x300B,  // 《 ↔ 》
        0x300B => 0x300A,
        0x300C => 0x300D,  // 「 ↔ 」
        0x300D => 0x300C,
        0x300E => 0x300F,  // 『 ↔ 』
        0x300F => 0x300E,
    ];

    /**
     * Determine Bidi class for a codepoint via range tables. Subset of UCD —
     * covers Latin, Cyrillic, Arabic, Hebrew, digits, common punctuation,
     * whitespace.
     */
    public static function bidiClass(int $cp): string
    {
        // ASCII range — fast path.
        if ($cp < 0x80) {
            return self::asciiBidiClass($cp);
        }
        // Hebrew.
        if ($cp >= 0x0590 && $cp <= 0x05FF) {
            // Hebrew points are NSM, letters are R.
            if (($cp >= 0x0591 && $cp <= 0x05BD)
                || $cp === 0x05BF
                || ($cp >= 0x05C1 && $cp <= 0x05C2)
                || ($cp >= 0x05C4 && $cp <= 0x05C5)
                || $cp === 0x05C7) {
                return self::NSM;
            }

            return self::R;
        }
        // Arabic block and extensions.
        if (($cp >= 0x0600 && $cp <= 0x06FF)
            || ($cp >= 0x0750 && $cp <= 0x077F)
            || ($cp >= 0x08A0 && $cp <= 0x08FF)
            || ($cp >= 0xFB50 && $cp <= 0xFDFF)
            || ($cp >= 0xFE70 && $cp <= 0xFEFF)) {
            // Arabic-Indic digits.
            if (($cp >= 0x0660 && $cp <= 0x0669) || ($cp >= 0x06F0 && $cp <= 0x06F9)) {
                return self::AN;
            }
            // Arabic harakat (diacritics) are NSM.
            if (($cp >= 0x064B && $cp <= 0x065F)
                || $cp === 0x0670
                || ($cp >= 0x06D6 && $cp <= 0x06DC)
                || ($cp >= 0x06DF && $cp <= 0x06E4)
                || ($cp >= 0x06E7 && $cp <= 0x06E8)
                || ($cp >= 0x06EA && $cp <= 0x06ED)
                || $cp === 0x08D3) {
                return self::NSM;
            }

            return self::AL;
        }
        // Combining marks (general).
        if ($cp >= 0x0300 && $cp <= 0x036F) {
            return self::NSM;
        }
        // Latin Extended A/B, Cyrillic, Greek, etc. — all L.
        if ($cp >= 0x0100 && $cp <= 0x058F) {
            return self::L;
        }
        // Above Hebrew/Arabic — assume L for most other Latin/CJK scripts.
        if ($cp >= 0x0900 && $cp <= 0xFFFF) {
            return self::L;
        }

        return self::ON;
    }

    private static function asciiBidiClass(int $cp): string
    {
        // Whitespace.
        if ($cp === 0x09) {
            return self::S;
        }
        if ($cp === 0x0A || $cp === 0x0D) {
            return self::B;
        }
        if ($cp === 0x0B || $cp === 0x0C) {
            return self::S;
        }
        if ($cp === 0x20) {
            return self::WS;
        }
        // Digits.
        if ($cp >= 0x30 && $cp <= 0x39) {
            return self::EN;
        }
        // Letters.
        if (($cp >= 0x41 && $cp <= 0x5A) || ($cp >= 0x61 && $cp <= 0x7A)) {
            return self::L;
        }
        // ES (+ -)
        if ($cp === 0x2B || $cp === 0x2D) {
            return self::ES;
        }
        // ET (# $ % * °)
        if ($cp === 0x23 || $cp === 0x24 || $cp === 0x25) {
            return self::ET;
        }
        // CS (. , : / )
        if ($cp === 0x2E || $cp === 0x2C || $cp === 0x3A || $cp === 0x2F) {
            return self::CS;
        }
        // ON — brackets, quotes, etc.
        return self::ON;
    }

    /** @param list<string> $types */
    public static function detectParagraphLevel(array $types): int
    {
        foreach ($types as $t) {
            if ($t === self::L) {
                return 0;
            }
            if ($t === self::R || $t === self::AL) {
                return 1;
            }
        }

        return 0; // default LTR
    }

    /**
     * W rules — weak character type resolution (per UAX 9 §3.3.3).
     *
     * @param  list<string>  $types  modified in-place
     */
    /**
     * UAX 9 §3.3 X1-X10 — explicit embedding/override stack.
     *
     * Processes:
     *   LRE U+202A — Left-to-Right Embedding (push even level)
     *   RLE U+202B — Right-to-Left Embedding (push odd level)
     *   LRO U+202D — Left-to-Right Override (push even level + override)
     *   RLO U+202E — Right-to-Left Override (push odd level + override)
     *   PDF U+202C — Pop Directional Formatting
     *   LRI U+2066 — Left-to-Right Isolate
     *   RLI U+2067 — Right-to-Left Isolate
     *   FSI U+2068 — First Strong Isolate
     *   PDI U+2069 — Pop Directional Isolate
     *
     * Stack depth limit: 125 levels (max embedding level). Overflow → ignore.
     *
     * Output: filtered cps (X9 removes formatting chars) with per-char explicit
     * level + post-X type (overrides change strong types).
     *
     * @param  list<int>  $cps
     * @param  list<string>  $types
     * @return array{cps: list<int>, types: list<string>, levels: list<int>}
     */
    private static function applyExplicitLevels(array $cps, array $types, int $paragraphLevel): array
    {
        $maxLevel = 125;
        // Stack entry: ['level' => int, 'override' => null|'L'|'R', 'isolate' => bool]
        $stack = [['level' => $paragraphLevel, 'override' => null, 'isolate' => false]];
        $overflowEmbedding = 0;
        $overflowIsolate = 0;
        $validIsolateCount = 0;

        $outCps = [];
        $outTypes = [];
        $outLevels = [];

        foreach ($cps as $i => $cp) {
            $cls = $types[$i];
            $top = $stack[count($stack) - 1];

            // X2..X5: explicit embedding/override push.
            if ($cp === 0x202A || $cp === 0x202B || $cp === 0x202D || $cp === 0x202E) {
                $isRtl = ($cp === 0x202B || $cp === 0x202E);
                $override = ($cp === 0x202D) ? 'L' : (($cp === 0x202E) ? 'R' : null);
                // Next greater (even for LTR, odd for RTL) level.
                $newLevel = $isRtl
                    ? ($top['level'] + 1) | 1
                    : ($top['level'] + 2) & ~1;
                if ($newLevel <= $maxLevel && $overflowIsolate === 0 && $overflowEmbedding === 0) {
                    $stack[] = ['level' => $newLevel, 'override' => $override, 'isolate' => false];
                } else {
                    $overflowEmbedding++;
                }

                continue; // X9: formatting char removed from output
            }
            // X5a..X5c: isolate push.
            if ($cp === 0x2066 || $cp === 0x2067 || $cp === 0x2068) {
                $isRtl = ($cp === 0x2067);
                // FSI (0x2068): scan ahead first strong → L or R.
                if ($cp === 0x2068) {
                    $isRtl = self::fsiDirection($cps, $i, $types);
                }
                // Output isolate char itself with current level (UAX 9: isolates get level).
                $outCps[] = $cp;
                $outTypes[] = $isRtl ? self::R : self::L;
                $outLevels[] = $top['level'];
                $newLevel = $isRtl
                    ? ($top['level'] + 1) | 1
                    : ($top['level'] + 2) & ~1;
                if ($newLevel <= $maxLevel && $overflowIsolate === 0 && $overflowEmbedding === 0) {
                    $stack[] = ['level' => $newLevel, 'override' => null, 'isolate' => true];
                    $validIsolateCount++;
                } else {
                    $overflowIsolate++;
                }

                continue;
            }
            // X6a: PDI — pop to last isolate.
            if ($cp === 0x2069) {
                if ($overflowIsolate > 0) {
                    $overflowIsolate--;
                } elseif ($validIsolateCount > 0) {
                    $overflowEmbedding = 0;
                    while (count($stack) > 1 && ! $stack[count($stack) - 1]['isolate']) {
                        array_pop($stack);
                    }
                    if (count($stack) > 1) {
                        array_pop($stack);
                        $validIsolateCount--;
                    }
                }
                $top = $stack[count($stack) - 1];
                // Output PDI itself.
                $outCps[] = $cp;
                $outTypes[] = self::ON;
                $outLevels[] = $top['level'];

                continue;
            }
            // X7: PDF — pop one embedding level.
            if ($cp === 0x202C) {
                if ($overflowIsolate > 0) {
                    // nothing — outer isolate has overflowed embeddings
                } elseif ($overflowEmbedding > 0) {
                    $overflowEmbedding--;
                } elseif (count($stack) > 1 && ! $stack[count($stack) - 1]['isolate']) {
                    array_pop($stack);
                }

                continue;
            }
            // X6: regular char.
            $top = $stack[count($stack) - 1];
            $effectiveCls = $cls;
            if ($top['override'] !== null && in_array($cls, [self::L, self::R, self::AL, self::EN, self::AN], true)) {
                $effectiveCls = $top['override'];
            }
            $outCps[] = $cp;
            $outTypes[] = $effectiveCls;
            $outLevels[] = $top['level'];
        }

        return ['cps' => $outCps, 'types' => $outTypes, 'levels' => $outLevels];
    }

    /**
     * FSI direction detection — scan forward to first strong char before
     * matching PDI or end of input.
     *
     * @param  list<int>  $cps
     * @param  list<string>  $types
     */
    private static function fsiDirection(array $cps, int $startIdx, array $types): bool
    {
        $depth = 0;
        $n = count($cps);
        for ($j = $startIdx + 1; $j < $n; $j++) {
            $cp = $cps[$j];
            if ($cp === 0x2066 || $cp === 0x2067 || $cp === 0x2068) {
                $depth++;
            } elseif ($cp === 0x2069) {
                if ($depth === 0) {
                    break;
                }
                $depth--;
            } elseif ($depth === 0) {
                $cls = $types[$j];
                if ($cls === self::L) {
                    return false; // LTR
                }
                if ($cls === self::R || $cls === self::AL) {
                    return true; // RTL
                }
            }
        }

        return false; // default LTR if no strong char
    }

    /**
     * Implicit level assignment using explicit base levels
     * (post-X step) instead of single paragraph level.
     *
     * @param  list<string>  $types
     * @param  list<int>  $baseLevels  per-char explicit level from X-step
     * @return list<int>
     */
    private static function applyIWithExplicitBase(array $types, array $baseLevels): array
    {
        $levels = [];
        foreach ($types as $i => $t) {
            $base = $baseLevels[$i] ?? 0;
            if (($base & 1) === 0) {
                $levels[] = match ($t) {
                    self::L => $base,
                    self::R => $base + 1,
                    self::AN, self::EN => $base + 2,
                    default => $base,
                };
            } else {
                $levels[] = match ($t) {
                    self::R => $base,
                    self::L, self::AN, self::EN => $base + 1,
                    default => $base,
                };
            }
        }

        return $levels;
    }

    /**
     * @param  list<string>  $types  per-char bidi types (modified in-place)
     */
    private static function applyW(array &$types, int $paragraphLevel): void
    {
        $n = count($types);
        if ($n === 0) {
            return;
        }
        // W1: NSM takes type of previous character (or sor for first).
        $sor = $paragraphLevel === 0 ? self::L : self::R;
        $prev = $sor;
        for ($i = 0; $i < $n; $i++) {
            if ($types[$i] === self::NSM) {
                $types[$i] = $prev;
            } else {
                $prev = $types[$i];
            }
        }
        // W2: EN preceded (via ET, CS, etc.) by AL becomes AN.
        for ($i = 0; $i < $n; $i++) {
            if ($types[$i] !== self::EN) {
                continue;
            }
            // Walk back through neutrals/weaks to previous strong.
            for ($j = $i - 1; $j >= 0; $j--) {
                $tj = $types[$j];
                if ($tj === self::L || $tj === self::R) {
                    break;
                }
                if ($tj === self::AL) {
                    $types[$i] = self::AN;
                    break;
                }
            }
        }
        // W3: AL → R unconditionally.
        for ($i = 0; $i < $n; $i++) {
            if ($types[$i] === self::AL) {
                $types[$i] = self::R;
            }
        }
        // W4: ES/CS surrounded by EN's → EN. CS surrounded by AN's → AN.
        for ($i = 1; $i < $n - 1; $i++) {
            if ($types[$i] === self::ES && $types[$i - 1] === self::EN && $types[$i + 1] === self::EN) {
                $types[$i] = self::EN;
            } elseif ($types[$i] === self::CS) {
                if ($types[$i - 1] === self::EN && $types[$i + 1] === self::EN) {
                    $types[$i] = self::EN;
                } elseif ($types[$i - 1] === self::AN && $types[$i + 1] === self::AN) {
                    $types[$i] = self::AN;
                }
            }
        }
        // W5: ET adjacent (run) to EN → EN.
        for ($i = 0; $i < $n; $i++) {
            if ($types[$i] !== self::ET) {
                continue;
            }
            // Look forward for EN.
            $hasEnNeighbor = false;
            for ($j = $i + 1; $j < $n; $j++) {
                if ($types[$j] === self::ET) {
                    continue;
                }
                if ($types[$j] === self::EN) {
                    $hasEnNeighbor = true;
                }
                break;
            }
            // Look backward.
            if (! $hasEnNeighbor) {
                for ($j = $i - 1; $j >= 0; $j--) {
                    if ($types[$j] === self::ET) {
                        continue;
                    }
                    if ($types[$j] === self::EN) {
                        $hasEnNeighbor = true;
                    }
                    break;
                }
            }
            if ($hasEnNeighbor) {
                $types[$i] = self::EN;
            }
        }
        // W6: separators and terminators between non-EN/AN → ON.
        for ($i = 0; $i < $n; $i++) {
            if (in_array($types[$i], [self::ES, self::ET, self::CS], true)) {
                $types[$i] = self::ON;
            }
        }
        // W7: EN preceded by L (via neutrals) becomes L.
        for ($i = 0; $i < $n; $i++) {
            if ($types[$i] !== self::EN) {
                continue;
            }
            for ($j = $i - 1; $j >= 0; $j--) {
                $tj = $types[$j];
                if ($tj === self::L) {
                    $types[$i] = self::L;
                    break;
                }
                if ($tj === self::R) {
                    break;
                }
            }
        }
    }

    /**
     * N rules — neutral resolution (per UAX 9 §3.3.4).
     * Simplified: N1+N2 combined without bracket pair handling (N0).
     *
     * @param  list<string>  $types
     */
    private static function applyN(array &$types, int $paragraphLevel): void
    {
        $n = count($types);
        $neutral = [self::B, self::S, self::WS, self::ON];
        // Find runs of consecutive neutrals and assign based on surrounding strong types.
        $sorR = $paragraphLevel === 1 ? self::R : self::L;
        $i = 0;
        while ($i < $n) {
            if (! in_array($types[$i], $neutral, true)) {
                $i++;

                continue;
            }
            $runStart = $i;
            while ($i < $n && in_array($types[$i], $neutral, true)) {
                $i++;
            }
            $runEnd = $i - 1;

            // Previous strong (treat AN/EN as R for N rules).
            $prevStrong = $sorR;
            for ($j = $runStart - 1; $j >= 0; $j--) {
                $tj = $types[$j];
                if ($tj === self::L) {
                    $prevStrong = self::L;
                    break;
                }
                if ($tj === self::R || $tj === self::EN || $tj === self::AN) {
                    $prevStrong = self::R;
                    break;
                }
            }
            // Next strong.
            $nextStrong = $sorR;
            for ($j = $runEnd + 1; $j < $n; $j++) {
                $tj = $types[$j];
                if ($tj === self::L) {
                    $nextStrong = self::L;
                    break;
                }
                if ($tj === self::R || $tj === self::EN || $tj === self::AN) {
                    $nextStrong = self::R;
                    break;
                }
            }
            // N1: if matched, take that direction. Else N2: take paragraph direction.
            $direction = $prevStrong === $nextStrong ? $prevStrong : ($paragraphLevel === 1 ? self::R : self::L);
            for ($k = $runStart; $k <= $runEnd; $k++) {
                $types[$k] = $direction;
            }
        }
    }


    /**
     * L2 — reverse contiguous spans of chars at level ≥ k for k from
     * max(levels) down to lowest odd level.
     *
     * @param  list<int>  $cps
     * @param  list<int>  $levels
     * @return list<int>
     */
    private static function applyL2(array $cps, array $levels): array
    {
        if ($cps === []) {
            return [];
        }
        $maxLevel = max($levels);
        if ($maxLevel === 0) {
            return $cps;
        }
        $result = $cps;
        $n = count($result);
        for ($k = $maxLevel; $k >= 1; $k--) {
            $i = 0;
            while ($i < $n) {
                if ($levels[$i] >= $k) {
                    $start = $i;
                    while ($i < $n && $levels[$i] >= $k) {
                        $i++;
                    }
                    // Reverse $result[$start..$i-1].
                    $span = array_slice($result, $start, $i - $start);
                    $reversed = array_reverse($span);
                    array_splice($result, $start, $i - $start, $reversed);
                } else {
                    $i++;
                }
            }
        }

        return $result;
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
