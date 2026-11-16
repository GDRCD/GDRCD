<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\Alignment;

/**
 * Mathematical expression — LaTeX-like subset.
 *
 * Supported syntax:
 *  - `a^{b}` / `a^b`     — superscript
 *  - `a_{b}` / `a_b`     — subscript
 *  - `\frac{n}{d}`       — fraction
 *  - `\sqrt{x}`          — square root
 *  - Greek letters: `\alpha`, `\beta`, `\pi`, `\theta`, `\Sigma`, ...
 *  - Operators: `\cdot`, `\times`, `\div`, `\pm`, `\leq`, `\geq`, `\neq`,
 *    `\approx`, `\infty`, `\sum`, `\int`
 *  - Big operators with limits, multi-line equations
 *  - Matrices / arrays: `matrix`, `pmatrix`, `bmatrix`, `vmatrix`
 *  - LaTeX environments: `\begin{align}...\end{align}` (and `aligned`,
 *    `gather`, `eqnarray`, `cases`, matrix variants)
 *
 * Rendered as a block, default centered. Subscripts and superscripts use
 * 70% font size with a baseline shift. Custom font family resolves
 * through the engine's FontProvider; null falls back to the engine
 * default font.
 */
final readonly class MathExpression implements BlockElement
{
    public function __construct(
        public string $tex,
        public float $fontSizePt = 12.0,
        public Alignment $alignment = Alignment::Center,
        public float $spaceBeforePt = 4.0,
        public float $spaceAfterPt = 4.0,
        public ?string $fontFamily = null,
    ) {
        if ($tex === '') {
            throw new \InvalidArgumentException('MathExpression requires non-empty TeX input');
        }
    }
}
