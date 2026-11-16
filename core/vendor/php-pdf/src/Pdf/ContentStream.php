<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * Builder for the Page content stream — the graphical PostScript-style
 * subset of PDF (ISO 32000-1 § 7.8).
 *
 * Operators (minimal set):
 *   q / Q     — push / pop graphics state
 *   BT / ET   — begin / end text object
 *   Tf        — set font + size (`/F1 12 Tf`)
 *   Td        — move text position (`x y Td`, in pt from origin)
 *   Tj        — show text string (`(Hello) Tj`)
 *   re / S / f — rectangle / stroke / fill (for rectangles)
 *   rg        — set non-stroking RGB color (`0.5 0.5 0.5 rg`)
 *
 * Coordinate system: origin at the **bottom-left** corner of the page; X grows
 * right, Y grows up. This is opposite of CSS / screen coordinates.
 * Unit is 1 pt (1/72 inch).
 */
final class ContentStream
{
    private string $body = '';

    // Cache last emitted graphics state. Drop redundant q/rg/Q
    // wraps when consecutive operations use the same fill color.
    private ?float $lastFillR = null;
    private ?float $lastFillG = null;
    private ?float $lastFillB = null;

    /**
     * Text operation. Coordinates in pt from origin (bottom-left).
     */
    public function text(
        string $fontName, float $sizePt, float $xPt, float $yPt, string $text,
        ?float $r = null, ?float $g = null, ?float $b = null,
        float $letterSpacingPt = 0,
    ): self {
        $escapedText = $this->escapeString($text);
        $this->openTextColor($r, $g, $b);
        $this->body .= 'BT'."\n";
        $this->body .= sprintf("/%s %s Tf\n", $fontName, $this->formatNumber($sizePt));
        if ($letterSpacingPt !== 0.0) {
            $this->body .= sprintf("%s Tc\n", $this->formatNumber($letterSpacingPt));
        }
        $this->body .= sprintf("%s %s Td\n", $this->formatNumber($xPt), $this->formatNumber($yPt));
        $this->body .= sprintf("(%s) Tj\n", $escapedText);
        $this->body .= 'ET'."\n";
        $this->closeTextColor($r);

        return $this;
    }

    /**
     * Text with a pre-encoded hex glyph-ID string (for Type0 composite fonts
     * with Identity-H encoding). $hexString must include angle brackets:
     *   `<00480065006C006C006F>` — encoded "Hello"
     */
    public function textHexString(
        string $fontName, float $sizePt, float $xPt, float $yPt, string $hexString,
        ?float $r = null, ?float $g = null, ?float $b = null,
        float $letterSpacingPt = 0,
    ): self {
        $this->openTextColor($r, $g, $b);
        $this->body .= 'BT'."\n";
        $this->body .= sprintf("/%s %s Tf\n", $fontName, $this->formatNumber($sizePt));
        if ($letterSpacingPt !== 0.0) {
            $this->body .= sprintf("%s Tc\n", $this->formatNumber($letterSpacingPt));
        }
        $this->body .= sprintf("%s %s Td\n", $this->formatNumber($xPt), $this->formatNumber($yPt));
        $this->body .= sprintf("%s Tj\n", $hexString);
        $this->body .= 'ET'."\n";
        $this->closeTextColor($r);

        return $this;
    }

    /**
     * Text with kerning via the PDF `TJ` operator (shows glyphs with
     * inter-glyph position adjustments).
     *
     * $tjOps — alternating list<string|int>:
     *  - string '<NNNN...>' — hex glyph-ID run (shown via TJ)
     *  - int N — position adjustment in 1000/em units (positive = move
     *    next glyph LEFT, less space between chars; e.g. kerning AV)
     *
     * Example for kerned "AVA":
     *   $cs->textTjArray('F1', 12, 72, 720, ['<0036>', 74, '<00570036>']);
     *   // → BT /F1 12 Tf 72 720 Td [<0036> 74 <00570036>] TJ ET
     *
     * @param  list<string|int>  $tjOps
     */
    public function textTjArray(
        string $fontName, float $sizePt, float $xPt, float $yPt, array $tjOps,
        ?float $r = null, ?float $g = null, ?float $b = null,
    ): self {
        $this->openTextColor($r, $g, $b);
        $this->body .= 'BT'."\n";
        $this->body .= sprintf("/%s %s Tf\n", $fontName, $this->formatNumber($sizePt));
        $this->body .= sprintf("%s %s Td\n", $this->formatNumber($xPt), $this->formatNumber($yPt));
        $this->body .= '[';
        foreach ($tjOps as $op) {
            if (is_int($op)) {
                $this->body .= ' '.$op.' ';
            } else {
                $this->body .= $op; // already-wrapped <hex>
            }
        }
        $this->body .= "] TJ\n";
        $this->body .= 'ET'."\n";
        $this->closeTextColor($r);

        return $this;
    }

    /**
     * Persistent fill-color tracking:
     *  - Drop q/Q wrap entirely (rely on PDF gstate persistence)
     *  - Emit `rg` ONLY when color actually changes
     *  - fillRectangle and similar ops wrap with their own q/Q (isolate their state)
     *
     * Caveat: caller is responsible for clean state — if some external code changes
     * the gstate fill color (fillRect inside its own q/Q is OK, but a persistent rg
     * is not), the tracker desyncs. All emitters in this class use a
     * q/Q wrap for their own state changes.
     */
    private function openTextColor(?float $r, ?float $g, ?float $b): void
    {
        if ($r === null) {
            return;
        }
        $g ??= 0;
        $b ??= 0;
        // Skip emit if color is already in gstate.
        if ($this->lastFillR === $r && $this->lastFillG === $g && $this->lastFillB === $b) {
            return;
        }
        $this->body .= sprintf("%s %s %s rg\n",
            $this->formatNumber($r),
            $this->formatNumber($g),
            $this->formatNumber($b),
        );
        $this->lastFillR = $r;
        $this->lastFillG = $g;
        $this->lastFillB = $b;
    }

    private function closeTextColor(?float $r): void
    {
        // No-op. Color persists in gstate — no need to restore.
        // Method kept for compatibility with existing call sites.
    }

    /**
     * Filled rectangle with RGB color (0..1).
     */
    public function fillRectangle(
        float $xPt,
        float $yPt,
        float $widthPt,
        float $heightPt,
        float $r = 0,
        float $g = 0,
        float $b = 0,
    ): self {
        $this->body .= 'q'."\n";
        $this->body .= sprintf("%s %s %s rg\n", $this->formatNumber($r), $this->formatNumber($g), $this->formatNumber($b));
        $this->body .= sprintf("%s %s %s %s re\n", $this->formatNumber($xPt), $this->formatNumber($yPt), $this->formatNumber($widthPt), $this->formatNumber($heightPt));
        $this->body .= 'f'."\n";
        $this->body .= 'Q'."\n";

        return $this;
    }

    /**
     * Stroked rectangle (outline only, no fill). RGB stroke color
     * 0..1, line width in pt.
     */
    /**
     * Generic path emission for SVG path support.
     *
     * Commands tuple per entry:
     *  - ['M', x, y]
     *  - ['L', x, y]
     *  - ['C', x1, y1, x2, y2, x3, y3]  cubic Bezier
     *  - 'Z' close
     *
     * \$mode: 'fill' | 'stroke' | 'fillstroke'.
     *
     * @param  list<array{0: string, 1: float, 2: float, 3?: float, 4?: float, 5?: float, 6?: float}|string>  $commands
     * @param  array{r: float, g: float, b: float}|null  $fillRgb
     * @param  array{r: float, g: float, b: float}|null  $strokeRgb
     */
    public function emitPath(
        array $commands,
        string $mode = 'fill',
        ?array $fillRgb = null,
        ?array $strokeRgb = null,
        float $lineWidthPt = 1.0,
    ): self {
        if ($commands === []) {
            return $this;
        }
        $this->body .= "q\n";
        if ($fillRgb !== null && ($mode === 'fill' || $mode === 'fillstroke')) {
            $this->body .= sprintf("%s %s %s rg\n",
                $this->formatNumber($fillRgb['r']),
                $this->formatNumber($fillRgb['g']),
                $this->formatNumber($fillRgb['b']),
            );
        }
        if ($strokeRgb !== null && ($mode === 'stroke' || $mode === 'fillstroke')) {
            $this->body .= sprintf("%s w\n", $this->formatNumber($lineWidthPt));
            $this->body .= sprintf("%s %s %s RG\n",
                $this->formatNumber($strokeRgb['r']),
                $this->formatNumber($strokeRgb['g']),
                $this->formatNumber($strokeRgb['b']),
            );
        }
        foreach ($commands as $cmd) {
            if ($cmd === 'Z') {
                $this->body .= "h\n";

                continue;
            }
            if (! is_array($cmd)) {
                continue;
            }
            $type = $cmd[0];
            if ($type === 'M') {
                $this->body .= sprintf("%s %s m\n",
                    $this->formatNumber($cmd[1]), $this->formatNumber($cmd[2]),
                );
            } elseif ($type === 'L') {
                $this->body .= sprintf("%s %s l\n",
                    $this->formatNumber($cmd[1]), $this->formatNumber($cmd[2]),
                );
            } elseif ($type === 'C') {
                $this->body .= sprintf("%s %s %s %s %s %s c\n",
                    $this->formatNumber($cmd[1]), $this->formatNumber($cmd[2]),
                    $this->formatNumber($cmd[3]), $this->formatNumber($cmd[4]),
                    $this->formatNumber($cmd[5]), $this->formatNumber($cmd[6]),
                );
            }
        }
        $op = match ($mode) {
            'stroke' => 'S',
            'fillstroke' => 'B',
            default => 'f',
        };
        $this->body .= $op."\n";
        $this->body .= "Q\n";

        return $this;
    }

    /**
     * Fill rectangle with shading pattern. Operators:
     *  /Pattern cs    — switch to pattern color space.
     *  /Pn scn        — use pattern resource named Pn.
     *  x y w h re f   — rect path + fill.
     */
    public function fillRectWithPattern(float $x, float $y, float $w, float $h, string $patternName): self
    {
        $this->body .= "q\n";
        $this->body .= "/Pattern cs\n";
        $this->body .= sprintf("/%s scn\n", $patternName);
        $this->body .= sprintf("%s %s %s %s re\nf\n",
            $this->formatNumber($x), $this->formatNumber($y),
            $this->formatNumber($w), $this->formatNumber($h),
        );
        $this->body .= "Q\n";

        return $this;
    }

    /**
     * Begin tagged marked content. Pairs with emitEndMarkedContent().
     */
    public function emitBeginMarkedContent(string $tag, int $mcid): self
    {
        $this->body .= sprintf("/%s << /MCID %d >> BDC\n", $tag, $mcid);

        return $this;
    }

    public function emitEndMarkedContent(): self
    {
        $this->body .= "EMC\n";

        return $this;
    }

    /**
     * Begin /Artifact marked content (PDF/UA — content
     * excluded from struct tree / screen readers). No MCID.
     */
    public function emitBeginArtifact(string $type = 'Pagination'): self
    {
        $this->body .= sprintf("/Artifact << /Type /%s >> BDC\n", $type);

        return $this;
    }

    /**
     * Filled polygon. Points = list<[x, y]>; closed path.
     *
     * @param  list<array{0: float, 1: float}>  $points
     */
    public function fillPolygon(array $points, float $r = 0, float $g = 0, float $b = 0): self
    {
        if (count($points) < 3) {
            return $this;
        }
        $this->body .= 'q'."\n";
        $this->body .= sprintf("%s %s %s rg\n", $this->formatNumber($r), $this->formatNumber($g), $this->formatNumber($b));
        $this->body .= sprintf("%s %s m\n", $this->formatNumber($points[0][0]), $this->formatNumber($points[0][1]));
        for ($i = 1; $i < count($points); $i++) {
            $this->body .= sprintf("%s %s l\n", $this->formatNumber($points[$i][0]), $this->formatNumber($points[$i][1]));
        }
        $this->body .= "h\nf\nQ\n";

        return $this;
    }

    /**
     * Stroked polyline (NOT closed) for line charts.
     *
     * @param  list<array{0: float, 1: float}>  $points
     */
    public function strokePolyline(
        array $points,
        float $lineWidthPt = 1.0,
        float $r = 0, float $g = 0, float $b = 0,
    ): self {
        if (count($points) < 2) {
            return $this;
        }
        $this->body .= 'q'."\n";
        $this->body .= sprintf("%s w\n", $this->formatNumber($lineWidthPt));
        $this->body .= sprintf("%s %s %s RG\n", $this->formatNumber($r), $this->formatNumber($g), $this->formatNumber($b));
        $this->body .= sprintf("%s %s m\n", $this->formatNumber($points[0][0]), $this->formatNumber($points[0][1]));
        for ($i = 1; $i < count($points); $i++) {
            $this->body .= sprintf("%s %s l\n", $this->formatNumber($points[$i][0]), $this->formatNumber($points[$i][1]));
        }
        $this->body .= "S\nQ\n";

        return $this;
    }

    /**
     * Stroked straight line from (x1,y1) to (x2,y2).
     */
    public function strokeLine(
        float $x1, float $y1, float $x2, float $y2,
        float $lineWidthPt = 0.5,
        float $r = 0, float $g = 0, float $b = 0,
    ): self {
        $this->body .= 'q'."\n";
        $this->body .= sprintf("%s w\n", $this->formatNumber($lineWidthPt));
        $this->body .= sprintf("%s %s %s RG\n", $this->formatNumber($r), $this->formatNumber($g), $this->formatNumber($b));
        $this->body .= sprintf("%s %s m\n", $this->formatNumber($x1), $this->formatNumber($y1));
        $this->body .= sprintf("%s %s l\n", $this->formatNumber($x2), $this->formatNumber($y2));
        $this->body .= 'S'."\n";
        $this->body .= 'Q'."\n";

        return $this;
    }

    public function strokeRectangle(
        float $xPt,
        float $yPt,
        float $widthPt,
        float $heightPt,
        float $lineWidthPt = 0.5,
        float $r = 0,
        float $g = 0,
        float $b = 0,
    ): self {
        $this->body .= 'q'."\n";
        $this->body .= sprintf("%s w\n", $this->formatNumber($lineWidthPt));
        $this->body .= sprintf("%s %s %s RG\n", $this->formatNumber($r), $this->formatNumber($g), $this->formatNumber($b));
        $this->body .= sprintf("%s %s %s %s re\n", $this->formatNumber($xPt), $this->formatNumber($yPt), $this->formatNumber($widthPt), $this->formatNumber($heightPt));
        $this->body .= 'S'."\n";
        $this->body .= 'Q'."\n";

        return $this;
    }

    /**
     * Draw image (XObject) with translation + scale.
     *
     * PDF coords: image XObject is natively a 1×1 unit. To get
     * widthPt × heightPt at point (xPt, yPt) — CTM matrix:
     *   widthPt 0 0 heightPt xPt yPt cm
     * Image origin = bottom-left corner.
     *
     * @param  string  $name  Name in page /Resources/XObject << /Im1 ... >>
     */
    public function drawImage(string $name, float $xPt, float $yPt, float $widthPt, float $heightPt): self
    {
        $this->body .= "q\n";
        $this->body .= sprintf("%s 0 0 %s %s %s cm\n",
            $this->formatNumber($widthPt),
            $this->formatNumber($heightPt),
            $this->formatNumber($xPt),
            $this->formatNumber($yPt),
        );
        $this->body .= sprintf("/%s Do\n", $name);
        $this->body .= "Q\n";

        return $this;
    }

    /**
     * Paint a Form XObject via scale+translate CTM + `/Name Do`.
     */
    public function useFormXObject(string $name, float $sx, float $sy, float $tx, float $ty): self
    {
        $this->body .= "q\n";
        $this->body .= sprintf("%s 0 0 %s %s %s cm\n",
            $this->formatNumber($sx),
            $this->formatNumber($sy),
            $this->formatNumber($tx),
            $this->formatNumber($ty),
        );
        $this->body .= sprintf("/%s Do\n", $name);
        $this->body .= "Q\n";

        return $this;
    }

    /**
     * Begin Optional Content section — `/OC /name BDC`.
     */
    public function beginLayerContent(string $resourceName): self
    {
        $this->body .= sprintf("/OC /%s BDC\n", $resourceName);

        return $this;
    }

    /**
     * End Optional Content section — `EMC`.
     */
    public function endLayerContent(): self
    {
        $this->body .= "EMC\n";

        return $this;
    }

    /**
     * Set line dash pattern. `[a b c d] phase d`.
     * Empty array resets to solid line.
     *
     * @param  list<float>  $pattern  alternating on/off lengths (PDF units)
     */
    public function setLineDashPattern(array $pattern, float $phase = 0.0): self
    {
        $arr = implode(' ', array_map([$this, 'formatNumber'], $pattern));
        $this->body .= sprintf("[%s] %s d\n", $arr, $this->formatNumber($phase));

        return $this;
    }

    /** Reset to solid line. */
    public function resetLineDashPattern(): self
    {
        $this->body .= "[] 0 d\n";

        return $this;
    }

    /**
     * Set line cap style. 0=butt, 1=round, 2=projecting square.
     */
    public function setLineCap(int $cap): self
    {
        if ($cap < 0 || $cap > 2) {
            throw new \InvalidArgumentException('Line cap must be 0..2');
        }
        $this->body .= sprintf("%d J\n", $cap);

        return $this;
    }

    /**
     * Set line join style. 0=miter, 1=round, 2=bevel.
     */
    public function setLineJoin(int $join): self
    {
        if ($join < 0 || $join > 2) {
            throw new \InvalidArgumentException('Line join must be 0..2');
        }
        $this->body .= sprintf("%d j\n", $join);

        return $this;
    }

    /** Set miter limit (controls miter→bevel switchover). */
    public function setMiterLimit(float $limit): self
    {
        $this->body .= sprintf("%s M\n", $this->formatNumber($limit));

        return $this;
    }

    /**
     * Set a clipping rect for subsequent ops.
     *
     * Emits `q x y w h re W n` — pushes graphics state, builds rect,
     * marks it as a clip path (nonzero winding), discards stroke/fill.
     * Subsequent drawing is masked to the rect. End with popGraphicsState().
     */
    public function clipRect(float $x, float $y, float $w, float $h): self
    {
        $this->body .= "q\n";
        $this->body .= sprintf("%s %s %s %s re\nW n\n",
            $this->formatNumber($x), $this->formatNumber($y),
            $this->formatNumber($w), $this->formatNumber($h),
        );

        return $this;
    }

    /**
     * Set a clipping polygon. Uses non-zero winding rule.
     *
     * @param  list<array{0: float, 1: float}>  $points
     */
    public function clipPolygon(array $points): self
    {
        if (count($points) < 3) {
            throw new \InvalidArgumentException('Clip polygon needs ≥3 points');
        }
        $this->body .= "q\n";
        $first = true;
        foreach ($points as [$x, $y]) {
            $this->body .= sprintf("%s %s %s\n",
                $this->formatNumber($x), $this->formatNumber($y),
                $first ? 'm' : 'l',
            );
            $first = false;
        }
        $this->body .= "h W n\n";

        return $this;
    }

    /** Emit Q — restore graphics state (ends clip and everything). */
    public function endClip(): self
    {
        $this->body .= "Q\n";

        return $this;
    }

    /**
     * Set DeviceCMYK non-stroking (fill) color. Values 0..1.
     * Emits `c m y k k`.
     */
    public function setCmykFillColor(float $c, float $m, float $y, float $k): self
    {
        self::validateCmyk($c, $m, $y, $k);
        $this->body .= sprintf("%s %s %s %s k\n",
            $this->formatNumber($c), $this->formatNumber($m),
            $this->formatNumber($y), $this->formatNumber($k),
        );

        return $this;
    }

    /**
     * Set DeviceCMYK stroking color. Values 0..1.
     * Emits `c m y k K`.
     */
    public function setCmykStrokeColor(float $c, float $m, float $y, float $k): self
    {
        self::validateCmyk($c, $m, $y, $k);
        $this->body .= sprintf("%s %s %s %s K\n",
            $this->formatNumber($c), $this->formatNumber($m),
            $this->formatNumber($y), $this->formatNumber($k),
        );

        return $this;
    }

    private static function validateCmyk(float $c, float $m, float $y, float $k): void
    {
        foreach ([$c, $m, $y, $k] as $v) {
            if ($v < 0.0 || $v > 1.0) {
                throw new \InvalidArgumentException('CMYK components must be in range 0..1');
            }
        }
    }

    /**
     * Set text rendering mode.
     *
     * Modes (ISO 32000-1 §9.3.6 Table 106):
     *  - 0: fill (default)
     *  - 1: stroke (outline only)
     *  - 2: fill + stroke
     *  - 3: invisible (useful for a searchable OCR layer over a scanned image)
     *  - 4: fill + add to path for clipping
     *  - 5: stroke + add to path for clipping
     *  - 6: fill + stroke + add to path for clipping
     *  - 7: add to path for clipping only
     *
     * Emits `N Tr`.
     */
    public function setTextRenderingMode(int $mode): self
    {
        if ($mode < 0 || $mode > 7) {
            throw new \InvalidArgumentException('Text rendering mode must be 0..7');
        }
        $this->body .= sprintf("%d Tr\n", $mode);

        return $this;
    }

    /**
     * drawImage with rotation around (xPt + widthPt/2, yPt + heightPt/2).
     * angleRad — counter-clockwise (PDF convention).
     *
     * Composed CTM: T(cx, cy) · R(θ) · T(-cx, -cy) · S(w, h) · T(x, y).
     */
    public function drawImageRotated(
        string $name, float $xPt, float $yPt, float $widthPt, float $heightPt, float $angleRad,
    ): self {
        $cx = $xPt + $widthPt / 2;
        $cy = $yPt + $heightPt / 2;
        $cos = cos($angleRad);
        $sin = sin($angleRad);

        $this->body .= "q\n";
        // Translate to center, rotate, translate back, then scale + position.
        // Combined matrix: precompute final CTM analytically.
        // Image XObject is a 1×1 unit. Need transform: scale(w,h) → rotate around center → translate.
        // Equivalently: a=cos, b=sin, c=-sin, d=cos for rotation around origin
        // applied to scaled image, then translated so that center stays at (cx, cy).
        $a = $cos * $widthPt;
        $b = $sin * $widthPt;
        $c = -$sin * $heightPt;
        $d = $cos * $heightPt;
        $e = $cx - ($a + $c) / 2;
        $f = $cy - ($b + $d) / 2;
        $this->body .= sprintf("%s %s %s %s %s %s cm\n",
            $this->formatNumber($a), $this->formatNumber($b),
            $this->formatNumber($c), $this->formatNumber($d),
            $this->formatNumber($e), $this->formatNumber($f),
        );
        $this->body .= sprintf("/%s Do\n", $name);
        $this->body .= "Q\n";

        return $this;
    }

    /**
     * drawImage with an ExtGState (opacity) applied before the draw.
     * Operator sequence: q / gs / cm / Do / Q — Q restores graphics
     * state, so opacity does not leak to subsequent content.
     */
    public function drawImageWithGs(
        string $name,
        string $gsName,
        float $xPt,
        float $yPt,
        float $widthPt,
        float $heightPt,
    ): self {
        $this->body .= "q\n";
        $this->body .= sprintf("/%s gs\n", $gsName);
        $this->body .= sprintf("%s 0 0 %s %s %s cm\n",
            $this->formatNumber($widthPt),
            $this->formatNumber($heightPt),
            $this->formatNumber($xPt),
            $this->formatNumber($yPt),
        );
        $this->body .= sprintf("/%s Do\n", $name);
        $this->body .= "Q\n";

        return $this;
    }

    /**
     * Pushes a graphics state save and applies ExtGState by name.
     * Caller must pair with popGraphicsState() via PDF operator Q.
     */
    public function pushGraphicsStateWithGs(string $gsName): self
    {
        $this->body .= "q\n";
        $this->body .= sprintf("/%s gs\n", $gsName);

        return $this;
    }

    public function popGraphicsState(): self
    {
        $this->body .= "Q\n";

        return $this;
    }

    /**
     * Stroked rounded rectangle. Rounded corners via 4 cubic Bezier
     * arcs (quarter-circle approximation, control points = r × 0.5523).
     */
    public function strokeRoundedRectangle(
        float $xPt, float $yPt, float $widthPt, float $heightPt, float $radiusPt,
        float $lineWidthPt = 0.5,
        float $r = 0, float $g = 0, float $b = 0,
    ): self {
        $r0 = max(0, min($radiusPt, min($widthPt, $heightPt) / 2));
        $kappa = 0.55228474983;  // 4 × (sqrt(2) - 1) / 3
        $cp = $r0 * $kappa;

        $x = $xPt; $y = $yPt; $w = $widthPt; $h = $heightPt;
        $f = $this->formatNumber(...);
        $this->body .= 'q'."\n";
        $this->body .= sprintf("%s w\n", $f($lineWidthPt));
        $this->body .= sprintf("%s %s %s RG\n", $f($r), $f($g), $f($b));
        // Path: start at (x+r, y), follow rect's outer edge via arcs.
        $this->body .= sprintf("%s %s m\n", $f($x + $r0), $f($y));
        // Bottom edge → bottom-right corner.
        $this->body .= sprintf("%s %s l\n", $f($x + $w - $r0), $f($y));
        $this->body .= sprintf("%s %s %s %s %s %s c\n",
            $f($x + $w - $r0 + $cp), $f($y),
            $f($x + $w), $f($y + $r0 - $cp),
            $f($x + $w), $f($y + $r0),
        );
        // Right edge → top-right corner.
        $this->body .= sprintf("%s %s l\n", $f($x + $w), $f($y + $h - $r0));
        $this->body .= sprintf("%s %s %s %s %s %s c\n",
            $f($x + $w), $f($y + $h - $r0 + $cp),
            $f($x + $w - $r0 + $cp), $f($y + $h),
            $f($x + $w - $r0), $f($y + $h),
        );
        // Top edge → top-left corner.
        $this->body .= sprintf("%s %s l\n", $f($x + $r0), $f($y + $h));
        $this->body .= sprintf("%s %s %s %s %s %s c\n",
            $f($x + $r0 - $cp), $f($y + $h),
            $f($x), $f($y + $h - $r0 + $cp),
            $f($x), $f($y + $h - $r0),
        );
        // Left edge → bottom-left corner.
        $this->body .= sprintf("%s %s l\n", $f($x), $f($y + $r0));
        $this->body .= sprintf("%s %s %s %s %s %s c\n",
            $f($x), $f($y + $r0 - $cp),
            $f($x + $r0 - $cp), $f($y),
            $f($x + $r0), $f($y),
        );
        $this->body .= "h\nS\n";
        $this->body .= 'Q'."\n";

        return $this;
    }

    /**
     * Filled rounded rectangle.
     */
    public function fillRoundedRectangle(
        float $xPt, float $yPt, float $widthPt, float $heightPt, float $radiusPt,
        float $r = 0, float $g = 0, float $b = 0,
    ): self {
        $r0 = max(0, min($radiusPt, min($widthPt, $heightPt) / 2));
        $kappa = 0.55228474983;
        $cp = $r0 * $kappa;

        $x = $xPt; $y = $yPt; $w = $widthPt; $h = $heightPt;
        $f = $this->formatNumber(...);
        $this->body .= 'q'."\n";
        $this->body .= sprintf("%s %s %s rg\n", $f($r), $f($g), $f($b));
        $this->body .= sprintf("%s %s m\n", $f($x + $r0), $f($y));
        $this->body .= sprintf("%s %s l\n", $f($x + $w - $r0), $f($y));
        $this->body .= sprintf("%s %s %s %s %s %s c\n",
            $f($x + $w - $r0 + $cp), $f($y),
            $f($x + $w), $f($y + $r0 - $cp),
            $f($x + $w), $f($y + $r0),
        );
        $this->body .= sprintf("%s %s l\n", $f($x + $w), $f($y + $h - $r0));
        $this->body .= sprintf("%s %s %s %s %s %s c\n",
            $f($x + $w), $f($y + $h - $r0 + $cp),
            $f($x + $w - $r0 + $cp), $f($y + $h),
            $f($x + $w - $r0), $f($y + $h),
        );
        $this->body .= sprintf("%s %s l\n", $f($x + $r0), $f($y + $h));
        $this->body .= sprintf("%s %s %s %s %s %s c\n",
            $f($x + $r0 - $cp), $f($y + $h),
            $f($x), $f($y + $h - $r0 + $cp),
            $f($x), $f($y + $h - $r0),
        );
        $this->body .= sprintf("%s %s l\n", $f($x), $f($y + $r0));
        $this->body .= sprintf("%s %s %s %s %s %s c\n",
            $f($x), $f($y + $r0 - $cp),
            $f($x + $r0 - $cp), $f($y),
            $f($x + $r0), $f($y),
        );
        $this->body .= "h\nf\n";
        $this->body .= 'Q'."\n";

        return $this;
    }

    /**
     * Rotated text for watermarks. $angleRad — counter-clockwise angle
     * relative to the X-axis. Rotated around point (xPt, yPt). $color — RGB
     * 0..1.
     *
     * Uses text matrix Tm:
     *   cos(a) sin(a) -sin(a) cos(a) x y Tm
     *
     * For embedded fonts pass $hexString with angle brackets (Identity-H
     * encoded) + $isHex=true.
     */
    public function rotatedText(
        string $fontName,
        float $sizePt,
        float $xPt,
        float $yPt,
        float $angleRad,
        string $text,
        float $r = 0.85,
        float $g = 0.85,
        float $b = 0.85,
        bool $isHex = false,
    ): self {
        $cos = cos($angleRad);
        $sin = sin($angleRad);
        $this->body .= "q\n";
        $this->body .= sprintf("%s %s %s rg\n",
            $this->formatNumber($r),
            $this->formatNumber($g),
            $this->formatNumber($b),
        );
        $this->body .= 'BT'."\n";
        $this->body .= sprintf("/%s %s Tf\n", $fontName, $this->formatNumber($sizePt));
        $this->body .= sprintf("%s %s %s %s %s %s Tm\n",
            $this->formatNumber($cos),
            $this->formatNumber($sin),
            $this->formatNumber(-$sin),
            $this->formatNumber($cos),
            $this->formatNumber($xPt),
            $this->formatNumber($yPt),
        );
        if ($isHex) {
            $this->body .= sprintf("%s Tj\n", $text);
        } else {
            $this->body .= sprintf("(%s) Tj\n", $this->escapeString($text));
        }
        $this->body .= 'ET'."\n";
        $this->body .= 'Q'."\n";

        return $this;
    }

    public function toString(): string
    {
        return $this->body;
    }

    public function isEmpty(): bool
    {
        return $this->body === '';
    }

    /**
     * Escape a PDF literal string `(…)`. Per ISO 32000-1 §7.3.4.2:
     * must escape `\`, `(`, `)`. Also non-printable bytes → \nnn.
     */
    private function escapeString(string $s): string
    {
        $out = '';
        for ($i = 0; $i < strlen($s); $i++) {
            $c = $s[$i];
            $ord = ord($c);
            if ($c === '\\' || $c === '(' || $c === ')') {
                $out .= '\\'.$c;
            } elseif ($ord < 0x20 || $ord > 0x7E) {
                // Non-printable / non-ASCII: octal \nnn.
                $out .= sprintf("\\%03o", $ord);
            } else {
                $out .= $c;
            }
        }

        return $out;
    }

    /**
     * Without a locale-dependent float decimal separator.
     * Minimal trailing zeros.
     */
    private function formatNumber(float $n): string
    {
        if ((int) $n == $n) {
            return (string) (int) $n;
        }

        return rtrim(rtrim(sprintf('%.4f', $n), '0'), '.');
    }
}
