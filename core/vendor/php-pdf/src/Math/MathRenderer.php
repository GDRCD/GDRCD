<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Math;

use Dskripchenko\PhpPdf\Pdf\Page;
use Dskripchenko\PhpPdf\Pdf\PdfFont;
use Dskripchenko\PhpPdf\Pdf\StandardFont;

/**
 * TeX-like math expression → PDF render.
 *
 * Two-pass:
 *  1. measureWidth() — compute total horizontal extent.
 *  2. render() — emit text + drawing operators at given (x, y) baseline.
 *
 * Symbol substitutions via self::GREEK + self::OPERATORS tables.
 */
final class MathRenderer
{
    /** Greek letter substitutions (LaTeX command → Unicode). */
    private const GREEK = [
        'alpha' => 'α', 'beta' => 'β', 'gamma' => 'γ', 'delta' => 'δ',
        'epsilon' => 'ε', 'zeta' => 'ζ', 'eta' => 'η', 'theta' => 'θ',
        'iota' => 'ι', 'kappa' => 'κ', 'lambda' => 'λ', 'mu' => 'μ',
        'nu' => 'ν', 'xi' => 'ξ', 'pi' => 'π', 'rho' => 'ρ',
        'sigma' => 'σ', 'tau' => 'τ', 'phi' => 'φ', 'chi' => 'χ',
        'psi' => 'ψ', 'omega' => 'ω',
        'Alpha' => 'Α', 'Beta' => 'Β', 'Gamma' => 'Γ', 'Delta' => 'Δ',
        'Epsilon' => 'Ε', 'Zeta' => 'Ζ', 'Eta' => 'Η', 'Theta' => 'Θ',
        'Iota' => 'Ι', 'Kappa' => 'Κ', 'Lambda' => 'Λ', 'Mu' => 'Μ',
        'Nu' => 'Ν', 'Xi' => 'Ξ', 'Pi' => 'Π', 'Rho' => 'Ρ',
        'Sigma' => 'Σ', 'Tau' => 'Τ', 'Phi' => 'Φ', 'Chi' => 'Χ',
        'Psi' => 'Ψ', 'Omega' => 'Ω',
    ];

    /** Operator substitutions. */
    private const OPERATORS = [
        'cdot' => '·', 'times' => '×', 'div' => '÷',
        'pm' => '±', 'mp' => '∓',
        'leq' => '≤', 'geq' => '≥', 'neq' => '≠', 'approx' => '≈',
        'equiv' => '≡', 'infty' => '∞',
        'sum' => '∑', 'int' => '∫', 'prod' => '∏',
        'forall' => '∀', 'exists' => '∃', 'in' => '∈', 'notin' => '∉',
        'subset' => '⊂', 'supset' => '⊃', 'cup' => '∪', 'cap' => '∩',
        'rightarrow' => '→', 'leftarrow' => '←', 'to' => '→',
    ];

    /**
     * Parse TeX string → token tree. Returns list of nodes:
     *  - ['type' => 'text', 'value' => string]
     *  - ['type' => 'sup', 'value' => list<node>]
     *  - ['type' => 'sub', 'value' => list<node>]
     *  - ['type' => 'frac', 'num' => list<node>, 'den' => list<node>]
     *  - ['type' => 'sqrt', 'value' => list<node>]
     *
     * @return list<array<string, mixed>>
     */
    public static function parse(string $tex): array
    {
        $pos = 0;
        $tokens = self::parseGroup($tex, $pos, false);

        // Combine big operators (\sum, \int, \prod, \lim,
        // \bigcup, \bigcap) with following sub/sup → 'bigop' token.
        return self::combineBigOperators($tokens);
    }

    /**
     * Convert LaTeX \begin{env}...\end{env} to internal syntax.
     *
     * Supported environments:
     *  - align, align*, aligned, gather, gather*, eqnarray — multi-line:
     *    content kept as-is (\\ separator already handled by parseLines split)
     *  - cases — converted to \matrix{ ... } for grid layout
     *  - matrix, pmatrix, bmatrix, vmatrix — converted to equivalent \matrix command
     */
    private static function preprocessEnvironments(string $tex): string
    {
        // Match \begin{name}...\end{name} pairs.
        return preg_replace_callback(
            '/\\\\begin\{([a-zA-Z*]+)\}(.*?)\\\\end\{\1\}/s',
            static function (array $m): string {
                $env = $m[1];
                $content = trim($m[2]);

                return match ($env) {
                    'align', 'align*', 'aligned', 'gather', 'gather*', 'eqnarray' => $content,
                    'cases' => '\\matrix{'.$content.'}',
                    'matrix', 'pmatrix', 'bmatrix', 'vmatrix' => '\\'.$env.'{'.$content.'}',
                    default => $content, // unknown environment — pass content through
                };
            },
            $tex,
        ) ?? $tex;
    }

    /**
     * @return list<list<array<string, mixed>>> one token list (see parse()) per line
     */
    public static function parseLines(string $tex): array
    {
        // Pre-process LaTeX environments \begin{name}...\end{name}.
        // Supported environments:
        //   align, align*, aligned, gather, gather* — multi-line (\\ separates)
        //   cases — caseswise function (rendered as rows; brace prepended)
        //   matrix, pmatrix, bmatrix, vmatrix — passed to matrix command
        $tex = self::preprocessEnvironments($tex);

        // Brace-aware split on `\\\\` (2 backslashes) at depth 0 only —
        // preserves \\\\ inside \matrix{...}.
        $rowStrs = [];
        $current = '';
        $depth = 0;
        $len = strlen($tex);
        for ($i = 0; $i < $len; $i++) {
            $c = $tex[$i];
            if ($c === '{') {
                $depth++;
            } elseif ($c === '}') {
                $depth--;
            } elseif ($depth === 0 && $c === '\\' && $i + 1 < $len && $tex[$i + 1] === '\\') {
                $rowStrs[] = $current;
                $current = '';
                $i++; // skip second backslash

                continue;
            }
            $current .= $c;
        }
        if ($current !== '') {
            $rowStrs[] = $current;
        }

        $rows = [];
        foreach ($rowStrs as $rowStr) {
            $rowStr = trim($rowStr);
            if ($rowStr === '') {
                continue;
            }
            $rows[] = self::parse($rowStr);
        }

        return $rows === [] ? [self::parse($tex)] : $rows;
    }

    /**
     * Walk tokens, merge sequences (text=∑/∏/∫, sub, sup?) or
     * (text, sup, sub?) → bigop token.
     *
     * @param  list<array<string, mixed>>  $tokens
     * @return list<array<string, mixed>>
     */
    private static function combineBigOperators(array $tokens): array
    {
        $bigOpChars = ['∑', '∏', '∫', "\u{22C3}", "\u{22C2}", "\u{2295}", "\u{2297}"]; // sum, prod, int, bigcup, bigcap, oplus, otimes
        $bigOpNames = ['lim']; // \lim — emitted as 'lim' text by unknown-command fallback.
        $out = [];
        $i = 0;
        $n = count($tokens);
        while ($i < $n) {
            $tok = $tokens[$i];
            $isBigOp = false;
            if ($tok['type'] === 'text') {
                if (in_array($tok['value'], $bigOpChars, true) || in_array($tok['value'], $bigOpNames, true)) {
                    $isBigOp = true;
                }
            }
            if (! $isBigOp) {
                $out[] = $tok;
                $i++;

                continue;
            }
            // Look ahead: collect optional sup and sub tokens.
            $sup = null;
            $sub = null;
            while ($i + 1 < $n && in_array($tokens[$i + 1]['type'], ['sup', 'sub'], true)) {
                $next = $tokens[$i + 1];
                if ($next['type'] === 'sup' && $sup === null) {
                    $sup = $next['value'];
                    $i++;
                } elseif ($next['type'] === 'sub' && $sub === null) {
                    $sub = $next['value'];
                    $i++;
                } else {
                    break;
                }
            }
            if ($sup !== null || $sub !== null) {
                $out[] = ['type' => 'bigop', 'symbol' => $tok['value'], 'sup' => $sup, 'sub' => $sub];
            } else {
                $out[] = $tok;
            }
            $i++;
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function parseGroup(string $tex, int &$pos, bool $insideBrace): array
    {
        $tokens = [];
        $textBuf = '';
        $flush = static function () use (&$tokens, &$textBuf): void {
            if ($textBuf !== '') {
                $tokens[] = ['type' => 'text', 'value' => $textBuf];
                $textBuf = '';
            }
        };

        $len = strlen($tex);
        while ($pos < $len) {
            $ch = $tex[$pos];
            if ($insideBrace && $ch === '}') {
                $pos++; // consume }
                $flush();

                return $tokens;
            }
            if ($ch === '^') {
                $flush();
                $pos++;
                $tokens[] = ['type' => 'sup', 'value' => self::parseArg($tex, $pos)];

                continue;
            }
            if ($ch === '_') {
                $flush();
                $pos++;
                $tokens[] = ['type' => 'sub', 'value' => self::parseArg($tex, $pos)];

                continue;
            }
            if ($ch === '\\') {
                $flush();
                $pos++;
                $cmdStart = $pos;
                while ($pos < $len && ctype_alpha($tex[$pos])) {
                    $pos++;
                }
                $cmd = substr($tex, $cmdStart, $pos - $cmdStart);
                $tokens = array_merge($tokens, self::expandCommand($cmd, $tex, $pos));

                continue;
            }
            if ($ch === '{') {
                $flush();
                $pos++; // consume {
                $sub = self::parseGroup($tex, $pos, true);
                $tokens = array_merge($tokens, $sub);

                continue;
            }
            $textBuf .= $ch;
            $pos++;
        }
        $flush();

        return $tokens;
    }

    /**
     * Parse single argument: either `{...}` group or single character.
     *
     * @return list<array<string, mixed>>
     */
    private static function parseArg(string $tex, int &$pos): array
    {
        if ($pos < strlen($tex) && $tex[$pos] === '{') {
            $pos++;

            return self::parseGroup($tex, $pos, true);
        }
        if ($pos < strlen($tex)) {
            if ($tex[$pos] === '\\') {
                $pos++;
                $cmdStart = $pos;
                while ($pos < strlen($tex) && ctype_alpha($tex[$pos])) {
                    $pos++;
                }
                $cmd = substr($tex, $cmdStart, $pos - $cmdStart);

                return self::expandCommand($cmd, $tex, $pos);
            }
            $tok = [['type' => 'text', 'value' => $tex[$pos]]];
            $pos++;

            return $tok;
        }

        return [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function expandCommand(string $cmd, string $tex, int &$pos): array
    {
        if (isset(self::GREEK[$cmd])) {
            return [['type' => 'text', 'value' => self::GREEK[$cmd]]];
        }
        if (isset(self::OPERATORS[$cmd])) {
            return [['type' => 'text', 'value' => self::OPERATORS[$cmd]]];
        }
        if ($cmd === 'frac') {
            $num = self::parseArg($tex, $pos);
            $den = self::parseArg($tex, $pos);

            return [['type' => 'frac', 'num' => $num, 'den' => $den]];
        }
        if ($cmd === 'sqrt') {
            $val = self::parseArg($tex, $pos);

            return [['type' => 'sqrt', 'value' => $val]];
        }
        if ($cmd === 'matrix' || $cmd === 'pmatrix' || $cmd === 'bmatrix' || $cmd === 'vmatrix') {
            // Matrix syntax — \\matrix{1 & 2 \\\\ 3 & 4}.
            // Capture raw content between balanced braces to preserve '&'
            // and '\\\\' separators (parser normally consumes `\\` as 2 empty
            // commands).
            $raw = self::readRawBracedContent($tex, $pos);
            $rows = self::splitMatrixRaw($raw);

            return [['type' => 'matrix', 'rows' => $rows, 'variant' => $cmd]];
        }
        // Unknown command — fallback to literal text.
        return [['type' => 'text', 'value' => $cmd]];
    }

    /**
     * Read balanced {...} content as raw string (preserves `\\`).
     */
    private static function readRawBracedContent(string $tex, int &$pos): string
    {
        if ($pos >= strlen($tex) || $tex[$pos] !== '{') {
            return '';
        }
        $pos++; // skip {
        $depth = 1;
        $start = $pos;
        while ($pos < strlen($tex) && $depth > 0) {
            if ($tex[$pos] === '{') {
                $depth++;
            } elseif ($tex[$pos] === '}') {
                $depth--;
                if ($depth === 0) {
                    break;
                }
            }
            $pos++;
        }
        $content = substr($tex, $start, $pos - $start);
        if ($pos < strlen($tex)) {
            $pos++; // skip closing }
        }

        return $content;
    }

    /**
     * Split raw matrix content `1 & 2 \\\\ 3 & 4` → rows of cells.
     * Each cell parsed via parseGroup for inner LaTeX support (nested
     * fractions, sup, etc.).
     *
     * @return list<list<list<array<string, mixed>>>>
     */
    private static function splitMatrixRaw(string $raw): array
    {
        // Split rows by `\\` (2 backslashes).
        $rowStrs = preg_split('@\\\\\\\\@', $raw);
        if ($rowStrs === false) {
            return [];
        }
        $rows = [];
        foreach ($rowStrs as $rowStr) {
            $cells = explode('&', $rowStr);
            $rowCells = [];
            foreach ($cells as $cellStr) {
                $cellStr = trim($cellStr);
                if ($cellStr === '') {
                    $rowCells[] = [];

                    continue;
                }
                $rowCells[] = self::parse($cellStr);
            }
            $rows[] = $rowCells;
        }

        return $rows;
    }


    /**
     * Measure total horizontal width.
     *
     * @param  list<array<string, mixed>>  $tokens
     */
    public static function measureWidth(array $tokens, float $fontSize, PdfFont|StandardFont $font): float
    {
        $w = 0.0;
        foreach ($tokens as $tok) {
            $w += self::measureToken($tok, $fontSize, $font);
        }

        return $w;
    }

    /**
     * @param  array<string, mixed>  $tok
     */
    private static function measureToken(array $tok, float $fontSize, PdfFont|StandardFont $font): float
    {
        $charWidth = $fontSize * 0.55;

        return match ($tok['type']) {
            'text' => mb_strlen($tok['value'], 'UTF-8') * $charWidth,
            'sup', 'sub' => self::measureWidth($tok['value'], $fontSize * 0.7, $font),
            'frac' => max(
                self::measureWidth($tok['num'], $fontSize * 0.85, $font),
                self::measureWidth($tok['den'], $fontSize * 0.85, $font),
            ) + 4.0,
            'sqrt' => self::measureWidth($tok['value'], $fontSize, $font) + $fontSize * 0.6,
            'matrix' => self::measureMatrix($tok['rows'], $tok['variant'], $fontSize, $font),
            'bigop' => max(
                mb_strlen($tok['symbol'], 'UTF-8') * $charWidth * 1.3,
                $tok['sup'] !== null ? self::measureWidth($tok['sup'], $fontSize * 0.7, $font) : 0,
                $tok['sub'] !== null ? self::measureWidth($tok['sub'], $fontSize * 0.7, $font) : 0,
            ),
            default => 0.0,
        };
    }

    /**
     * @param  list<list<list<array<string, mixed>>>>  $rows
     */
    private static function measureMatrix(array $rows, string $variant, float $fontSize, PdfFont|StandardFont $font): float
    {
        if ($rows === []) {
            return 0.0;
        }
        $cols = max(array_map('count', $rows));
        $colWidths = array_fill(0, $cols, 0.0);
        foreach ($rows as $row) {
            foreach ($row as $ci => $cell) {
                $w = self::measureWidth($cell, $fontSize, $font);
                if ($w > $colWidths[$ci]) {
                    $colWidths[$ci] = $w;
                }
            }
        }
        $cellGap = $fontSize * 0.4;
        $bracketGap = $variant === 'matrix' ? 0 : $fontSize * 0.5;

        return array_sum($colWidths) + ($cols - 1) * $cellGap + 2 * $bracketGap;
    }

    /**
     * Render token list at (x, baselineY). Returns final x after rendering.
     *
     * @param  list<array<string, mixed>>  $tokens
     */
    public static function render(
        array $tokens,
        Page $page,
        float $x,
        float $baselineY,
        float $fontSize,
        PdfFont|StandardFont $font,
    ): float {
        foreach ($tokens as $tok) {
            $x = self::renderToken($tok, $page, $x, $baselineY, $fontSize, $font);
        }

        return $x;
    }

    /**
     * @param  array<string, mixed>  $tok
     */
    private static function renderToken(
        array $tok,
        Page $page,
        float $x,
        float $baselineY,
        float $fontSize,
        PdfFont|StandardFont $font,
    ): float {
        switch ($tok['type']) {
            case 'text':
                $text = (string) $tok['value'];
                self::drawText($page, $text, $x, $baselineY, $fontSize, $font);

                return $x + mb_strlen($text, 'UTF-8') * $fontSize * 0.55;

            case 'sup':
                $supSize = $fontSize * 0.7;
                $supY = $baselineY + $fontSize * 0.35;
                $endX = self::render($tok['value'], $page, $x, $supY, $supSize, $font);

                return $endX;

            case 'sub':
                $subSize = $fontSize * 0.7;
                $subY = $baselineY - $fontSize * 0.15;
                $endX = self::render($tok['value'], $page, $x, $subY, $subSize, $font);

                return $endX;

            case 'frac':
                $smaller = $fontSize * 0.85;
                $numW = self::measureWidth($tok['num'], $smaller, $font);
                $denW = self::measureWidth($tok['den'], $smaller, $font);
                $maxW = max($numW, $denW);
                $lineY = $baselineY + $fontSize * 0.25;
                // Numerator above line.
                self::render($tok['num'], $page, $x + ($maxW - $numW) / 2, $lineY + $smaller * 0.1, $smaller, $font);
                // Denominator below line.
                self::render($tok['den'], $page, $x + ($maxW - $denW) / 2, $lineY - $smaller, $smaller, $font);
                // Fraction line.
                $page->strokeLine($x, $lineY, $x + $maxW, $lineY, 0.5, 0, 0, 0);

                return $x + $maxW + 2.0;

            case 'sqrt':
                $valW = self::measureWidth($tok['value'], $fontSize, $font);
                $radWidth = $fontSize * 0.5;
                // Radical sign √ (Unicode U+221A) — show inline.
                self::drawText($page, "\u{221A}", $x, $baselineY, $fontSize, $font);
                // Overline above value.
                $page->strokeLine(
                    $x + $radWidth, $baselineY + $fontSize * 0.85,
                    $x + $radWidth + $valW + 2, $baselineY + $fontSize * 0.85,
                    0.5, 0, 0, 0,
                );
                self::render($tok['value'], $page, $x + $radWidth, $baselineY, $fontSize, $font);

                return $x + $radWidth + $valW + 2;

            case 'matrix':
                return self::renderMatrix($tok['rows'], $tok['variant'], $page, $x, $baselineY, $fontSize, $font);

            case 'bigop':
                // Big operator with centered limits above/below.
                $sym = $tok['symbol'];
                $symW = mb_strlen($sym, 'UTF-8') * $fontSize * 0.55;
                $supSize = $fontSize * 0.7;
                $subSize = $fontSize * 0.7;
                $supW = $tok['sup'] !== null ? self::measureWidth($tok['sup'], $supSize, $font) : 0;
                $subW = $tok['sub'] !== null ? self::measureWidth($tok['sub'], $subSize, $font) : 0;
                $totalW = max($symW, $supW, $subW);

                // Symbol at center; sup above, sub below.
                $symX = $x + ($totalW - $symW) / 2;
                self::drawText($page, $sym, $symX, $baselineY, $fontSize * 1.2, $font);

                if ($tok['sup'] !== null) {
                    $supX = $x + ($totalW - $supW) / 2;
                    $supY = $baselineY + $fontSize * 0.95;
                    self::render($tok['sup'], $page, $supX, $supY, $supSize, $font);
                }
                if ($tok['sub'] !== null) {
                    $subX = $x + ($totalW - $subW) / 2;
                    $subY = $baselineY - $fontSize * 0.4;
                    self::render($tok['sub'], $page, $subX, $subY, $subSize, $font);
                }

                return $x + $totalW + $fontSize * 0.2;
        }

        return $x;
    }

    /**
     * Render matrix — grid of cells aligned by rows + columns,
     * with optional brackets/parentheses around.
     *
     * @param  list<list<list<array<string, mixed>>>>  $rows
     */
    private static function renderMatrix(
        array $rows, string $variant, Page $page, float $x, float $baselineY,
        float $fontSize, PdfFont|StandardFont $font,
    ): float {
        if ($rows === []) {
            return $x;
        }
        $cols = max(array_map('count', $rows));
        $colWidths = array_fill(0, $cols, 0.0);
        foreach ($rows as $row) {
            foreach ($row as $ci => $cell) {
                $w = self::measureWidth($cell, $fontSize, $font);
                if ($w > $colWidths[$ci]) {
                    $colWidths[$ci] = $w;
                }
            }
        }
        $cellGap = $fontSize * 0.4;
        $rowHeight = $fontSize * 1.4;
        $totalH = count($rows) * $rowHeight;
        $bracketW = $variant === 'matrix' ? 0 : $fontSize * 0.4;

        $topY = $baselineY + $rowHeight * (count($rows) - 1) * 0.5 + $fontSize * 0.3;
        $bottomY = $topY - $totalH;

        // Draw brackets / parens.
        if ($variant === 'pmatrix') {
            $leftCh = '(';
            $rightCh = ')';
        } elseif ($variant === 'bmatrix') {
            $leftCh = '[';
            $rightCh = ']';
        } elseif ($variant === 'vmatrix') {
            $leftCh = '|';
            $rightCh = '|';
        } else {
            $leftCh = $rightCh = null;
        }
        if ($leftCh !== null) {
            // Single tall char approximation — repeated chars.
            self::drawText($page, $leftCh, $x, $baselineY - $totalH / 2 + $fontSize * 0.2, $fontSize * 1.5, $font);
            $x += $bracketW;
        }

        // Render cells.
        $cellX = $x;
        foreach ($rows as $ri => $row) {
            $cellY = $topY - ($ri + 1) * $rowHeight + $fontSize * 0.3;
            $colX = $cellX;
            foreach ($colWidths as $ci => $colW) {
                $cell = $row[$ci] ?? [];
                $cellW = self::measureWidth($cell, $fontSize, $font);
                self::render($cell, $page, $colX + ($colW - $cellW) / 2, $cellY, $fontSize, $font);
                $colX += $colW + $cellGap;
            }
        }
        $x = $cellX + array_sum($colWidths) + ($cols - 1) * $cellGap;

        if ($rightCh !== null) {
            self::drawText($page, $rightCh, $x, $baselineY - $totalH / 2 + $fontSize * 0.2, $fontSize * 1.5, $font);
            $x += $bracketW;
        }

        return $x;
    }

    private static function drawText(Page $page, string $text, float $x, float $y, float $size, PdfFont|StandardFont $font): void
    {
        if ($font instanceof PdfFont) {
            $page->showEmbeddedText($text, $x, $y, $font, $size);
        } else {
            $page->showText($text, $x, $y, $font, $size);
        }
    }
}
