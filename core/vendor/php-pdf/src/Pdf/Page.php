<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

use Dskripchenko\PhpPdf\Image\PdfImage;
use Dskripchenko\PhpPdf\Style\Orientation;
use Dskripchenko\PhpPdf\Style\PaperSize;

/**
 * Single PDF page with accumulated draw commands.
 *
 * Scope: text + simple shapes + images, all on absolute-positioned
 * coordinates. No layout / wrapping / line-breaking — that is handled by
 * the Layout Engine.
 *
 * Coordinate system: PDF origin = lower-left corner. X grows rightward,
 * Y grows upward. Unit is 1 pt (1/72 inch).
 *
 * Used via Document::addPage():
 *
 *   $doc = Document::new(PaperSize::A4);
 *   $page = $doc->addPage();
 *   $page->showText('Hello', x: 72, y: 720, font: StandardFont::TimesRoman, sizePt: 12);
 */
final class Page
{
    /** Accumulates content stream commands — deferred render on toBytes(). */
    private ContentStream $stream;

    /** @var array<string, StandardFont> name → font (resource registration) */
    private array $standardFonts = [];

    /** @var array<string, PdfFont> name → embedded font */
    private array $embeddedFonts = [];

    /** @var array<string, PdfImage> name → image */
    private array $images = [];

    /** @var array<string, PdfExtGState> name → extGState */
    private array $extGStates = [];

    private int $extGStateCounter = 0;

    /** Per-page MCID counter (monotone increment). */
    private int $mcidCounter = 0;

    /** @var array<string, PdfPattern> name → pattern */
    private array $patterns = [];

    private int $patternCounter = 0;

    /** @var array<string, PdfFormXObject> name → form */
    private array $formXObjects = [];

    private int $formXObjectCounter = 0;

    /** @var array<string, PdfTilingPattern> name → tiling pattern */
    private array $tilingPatterns = [];

    private int $tilingPatternCounter = 0;

    /** @var array<string, PdfLayer> name (resource) → layer */
    private array $layerProperties = [];

    private int $layerCounter = 0;

    /** @var array{style: string, duration: float, dimension: string|null, direction: int|null}|null Page transition (slideshow effect) — emitted as /Trans dict. */
    private ?array $transition = null;

    /** Page rotation in degrees (0, 90, 180, 270). */
    private int $rotation = 0;

    /** @var array{0:float,1:float,2:float,3:float}|null /CropBox [llx lly urx ury]. */
    private ?array $cropBox = null;

    /** @var array{0:float,1:float,2:float,3:float}|null /BleedBox. */
    private ?array $bleedBox = null;

    /** @var array{0:float,1:float,2:float,3:float}|null /TrimBox. */
    private ?array $trimBox = null;

    /** @var array{0:float,1:float,2:float,3:float}|null /ArtBox. */
    private ?array $artBox = null;

    /**
     * Set page rotation. Multiple of 90.
     */
    public function setRotation(int $degrees): self
    {
        $normalized = (($degrees % 360) + 360) % 360;
        if ($normalized % 90 !== 0) {
            throw new \InvalidArgumentException('Page rotation must be multiple of 90');
        }
        $this->rotation = $normalized;

        return $this;
    }

    public function rotation(): int
    {
        return $this->rotation;
    }

    /**
     * Set /CropBox (visible/printable area). All values in PDF
     * points in bottom-left origin coordinate system. Spec: §14.11.2.
     */
    public function setCropBox(float $llx, float $lly, float $urx, float $ury): self
    {
        $this->cropBox = [$llx, $lly, $urx, $ury];

        return $this;
    }

    /**
     * Set /BleedBox (printed area incl. bleed past trim).
     */
    public function setBleedBox(float $llx, float $lly, float $urx, float $ury): self
    {
        $this->bleedBox = [$llx, $lly, $urx, $ury];

        return $this;
    }

    /**
     * Set /TrimBox (final trimmed page after print finishing).
     */
    public function setTrimBox(float $llx, float $lly, float $urx, float $ury): self
    {
        $this->trimBox = [$llx, $lly, $urx, $ury];

        return $this;
    }

    /**
     * Set /ArtBox (extent of meaningful artistic content).
     */
    public function setArtBox(float $llx, float $lly, float $urx, float $ury): self
    {
        $this->artBox = [$llx, $lly, $urx, $ury];

        return $this;
    }

    /** @return array{0:float,1:float,2:float,3:float}|null */
    public function cropBox(): ?array
    {
        return $this->cropBox;
    }

    /** @return array{0:float,1:float,2:float,3:float}|null */
    public function bleedBox(): ?array
    {
        return $this->bleedBox;
    }

    /** @return array{0:float,1:float,2:float,3:float}|null */
    public function trimBox(): ?array
    {
        return $this->trimBox;
    }

    /** @return array{0:float,1:float,2:float,3:float}|null */
    public function artBox(): ?array
    {
        return $this->artBox;
    }

    /** Auto-advance duration in seconds (display time). */
    private ?float $autoAdvanceDuration = null;

    /** JavaScript executed when page opens (/AA /O). */
    private ?string $openActionScript = null;

    /** JavaScript executed when page closes (/AA /C). */
    private ?string $closeActionScript = null;

    /**
     * Set JavaScript executed when this page becomes visible.
     */
    public function setOpenActionScript(string $script): self
    {
        $this->openActionScript = $script;

        return $this;
    }

    /**
     * Set JavaScript executed when reader navigates away from this page.
     */
    public function setCloseActionScript(string $script): self
    {
        $this->closeActionScript = $script;

        return $this;
    }

    public function openActionScript(): ?string
    {
        return $this->openActionScript;
    }

    public function closeActionScript(): ?string
    {
        return $this->closeActionScript;
    }

    /**
     * Form field tab navigation order. Affects Tab key cycling
     * through AcroForm widgets + links on the page.
     *
     * Values per PDF spec §12.5:
     *  - 'R' (row): top-to-bottom, left-to-right (visual default)
     *  - 'C' (column): left-to-right, top-to-bottom by column
     *  - 'S' (structure): follow Tagged PDF struct tree (PDF/UA recommends)
     */
    private ?string $tabOrder = null;

    public function setTabOrder(string $order): self
    {
        if (! in_array($order, ['R', 'C', 'S'], true)) {
            throw new \InvalidArgumentException("Tab order must be 'R', 'C' or 'S', got '$order'");
        }
        $this->tabOrder = $order;

        return $this;
    }

    public function tabOrder(): ?string
    {
        return $this->tabOrder;
    }

    /**
     * Set page transition effect.
     *
     * @param  string  $style  'Split'|'Blinds'|'Box'|'Wipe'|'Dissolve'|'Glitter'|'Fly'|'Push'|'Cover'|'Uncover'|'Fade'|'R'
     * @param  float  $duration  transition duration in seconds (default 1).
     * @param  string|null  $dimension  H|V (for Split/Blinds).
     * @param  int|null  $direction  0|90|180|270|315 (for directional transitions).
     */
    public function setTransition(string $style, float $duration = 1.0, ?string $dimension = null, ?int $direction = null): self
    {
        $this->transition = [
            'style' => $style,
            'duration' => $duration,
            'dimension' => $dimension,
            'direction' => $direction,
        ];

        return $this;
    }

    public function setAutoAdvance(float $seconds): self
    {
        $this->autoAdvanceDuration = $seconds;

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     *
     * @internal
     */
    public function transition(): ?array
    {
        return $this->transition;
    }

    /**
     * @internal
     */
    public function autoAdvanceDuration(): ?float
    {
        return $this->autoAdvanceDuration;
    }

    public function nextMcid(): int
    {
        return $this->mcidCounter++;
    }

    /**
     * Link annotations accumulated on this page.
     *
     * @var list<array{kind: 'uri'|'internal', x1: float, y1: float, x2: float, y2: float, target: string}>
     */
    private array $linkAnnotations = [];

    /**
     * AcroForm fields on this page.
     *
     * @var list<array<string, mixed>>
     */
    private array $formFields = [];

    private int $fontCounter = 0;

    private int $imageCounter = 0;

    /**
     * @param  array{0: float, 1: float}|null  $customDimensionsPt  [widthPt, heightPt]
     *                                                              in portrait orientation; orientation swap applied automatically.
     */
    public function __construct(
        public readonly PaperSize $paperSize,
        public readonly Orientation $orientation = Orientation::Portrait,
        public readonly ?array $customDimensionsPt = null,
    ) {
        $this->stream = new ContentStream;
    }

    public function widthPt(): float
    {
        if ($this->customDimensionsPt !== null) {
            return $this->orientation === Orientation::Portrait
                ? $this->customDimensionsPt[0]
                : $this->customDimensionsPt[1];
        }

        return $this->orientation->applyTo($this->paperSize)[0];
    }

    public function heightPt(): float
    {
        if ($this->customDimensionsPt !== null) {
            return $this->orientation === Orientation::Portrait
                ? $this->customDimensionsPt[1]
                : $this->customDimensionsPt[0];
        }

        return $this->orientation->applyTo($this->paperSize)[1];
    }

    /**
     * Show text using a PDF base-14 font. Encoding — WinAnsi (Latin-1).
     * For Cyrillic / Unicode use showEmbeddedText() with PdfFont.
     */
    public function showText(
        string $text, float $x, float $y, StandardFont $font, float $sizePt,
        ?float $r = null, ?float $g = null, ?float $b = null,
        float $letterSpacingPt = 0,
    ): self {
        $resourceName = $this->registerStandardFont($font);
        $this->stream->text($resourceName, $sizePt, $x, $y, $text, $r, $g, $b, $letterSpacingPt);

        return $this;
    }

    /**
     * Vertical text (CJK/East-Asian writing mode).
     *
     * Stacks chars top-to-bottom from (x, y), each char placed individually
     * with y-advance = lineHeightPt (default 1.2 × sizePt). Char x-position
     * is fixed — each glyph stays upright (not rotated like in
     * "sideways" labels).
     *
     * Suitable for traditional CJK vertical writing where each glyph is
     * upright and stacked. Mixed Latin/CJK works since chars are placed
     * individually.
     *
     * Note: PDF spec-compliant Type 0 CIDFont vertical (UniJIS-UTF16-V CMap +
     * /WMode 1 + vmtx table) yields smoother results, but this API is
     * font-agnostic and simpler for plain labels/certificates.
     */
    public function showTextVertical(
        string $text, float $x, float $y, StandardFont $font, float $sizePt,
        ?float $lineHeightPt = null,
        ?float $r = null, ?float $g = null, ?float $b = null,
    ): self {
        $lineHeightPt ??= $sizePt * 1.2;
        $chars = self::splitChars($text);
        $currentY = $y;
        foreach ($chars as $ch) {
            $this->showText($ch, $x, $currentY, $font, $sizePt, $r, $g, $b);
            $currentY -= $lineHeightPt;
        }

        return $this;
    }

    /**
     * Vertical text using embedded TTF font (for CJK / Unicode).
     */
    public function showEmbeddedTextVertical(
        string $text, float $x, float $y, PdfFont $font, float $sizePt,
        ?float $lineHeightPt = null,
        ?float $r = null, ?float $g = null, ?float $b = null,
    ): self {
        $lineHeightPt ??= $sizePt * 1.2;
        $chars = self::splitChars($text);
        $currentY = $y;
        foreach ($chars as $ch) {
            $this->showEmbeddedText($ch, $x, $currentY, $font, $sizePt, $r, $g, $b);
            $currentY -= $lineHeightPt;
        }

        return $this;
    }

    /**
     * Split UTF-8 string into individual characters (multi-byte safe).
     *
     * @return list<string>
     */
    private static function splitChars(string $text): array
    {
        // mb_str_split() never returns false on PHP 8+.
        return mb_str_split($text, 1, 'UTF-8');
    }

    /**
     * Show text using embedded TTF font (via PdfFont). Supports Unicode
     * (Cyrillic, Greek, etc).
     *
     * Auto-applies kerning when the font has a GPOS table (Liberation,
     * Noto and most modern fonts). Without kerning falls back to the
     * plain Tj operator.
     */
    public function showEmbeddedText(
        string $text, float $x, float $y, PdfFont $font, float $sizePt,
        ?float $r = null, ?float $g = null, ?float $b = null,
        float $letterSpacingPt = 0,
    ): self {
        $resourceName = $this->registerEmbeddedFont($font);
        $tjOps = $font->encodeTextTjArray($text);
        if (count($tjOps) === 1) {
            $this->stream->textHexString($resourceName, $sizePt, $x, $y, $tjOps[0], $r, $g, $b, $letterSpacingPt);
        } else {
            // textTjArray has no letter-spacing support (TJ kerning
            // performs adjustments itself); when letter-spacing is set fall
            // back to Tc + hex single string.
            if ($letterSpacingPt !== 0.0) {
                $hex = $font->encodeText($text);
                $this->stream->textHexString($resourceName, $sizePt, $x, $y, $hex, $r, $g, $b, $letterSpacingPt);
            } else {
                $this->stream->textTjArray($resourceName, $sizePt, $x, $y, $tjOps, $r, $g, $b);
            }
        }

        return $this;
    }

    /**
     * Drawn watermark — large diagonal text behind content. Use the
     * StandardFont variant for base14 or embedded for PdfFont.
     */
    public function drawWatermark(
        string $text,
        float $cx,
        float $cy,
        StandardFont $font,
        float $sizePt,
        float $angleRad = -0.7854,    // -45° (down-right diagonal)
        float $r = 0.88, float $g = 0.88, float $b = 0.88,
        ?float $opacity = null,
    ): self {
        $resourceName = $this->registerStandardFont($font);
        $gsName = $this->maybeRegisterOpacityGs($opacity);
        if ($gsName !== null) {
            $this->stream->pushGraphicsStateWithGs($gsName);
        }
        $this->stream->rotatedText($resourceName, $sizePt, $cx, $cy, $angleRad, $text, $r, $g, $b);
        if ($gsName !== null) {
            $this->stream->popGraphicsState();
        }

        return $this;
    }

    public function drawWatermarkEmbedded(
        string $text,
        float $cx,
        float $cy,
        PdfFont $font,
        float $sizePt,
        float $angleRad = -0.7854,
        float $r = 0.88, float $g = 0.88, float $b = 0.88,
        ?float $opacity = null,
    ): self {
        $resourceName = $this->registerEmbeddedFont($font);
        $hex = $font->encodeText($text);
        $gsName = $this->maybeRegisterOpacityGs($opacity);
        if ($gsName !== null) {
            $this->stream->pushGraphicsStateWithGs($gsName);
        }
        $this->stream->rotatedText($resourceName, $sizePt, $cx, $cy, $angleRad, $hex, $r, $g, $b, isHex: true);
        if ($gsName !== null) {
            $this->stream->popGraphicsState();
        }

        return $this;
    }

    /**
     * For opacity ∈ (0, 1) registers an ExtGState and returns its
     * resource name; null/1.0/out-of-range → null (no-op).
     */
    private function maybeRegisterOpacityGs(?float $opacity): ?string
    {
        if ($opacity === null || $opacity >= 1.0) {
            return null;
        }
        $clamped = max(0.0, $opacity);

        return $this->registerExtGState(new PdfExtGState(fillOpacity: $clamped, strokeOpacity: $clamped));
    }

    /**
     * Register shading pattern, return resource name.
     */
    public function registerShadingPattern(PdfPattern $pattern): string
    {
        $name = 'P'.(++$this->patternCounter);
        $this->patterns[$name] = $pattern;

        return $name;
    }

    /**
     * @return array<string, PdfPattern>
     *
     * @internal
     */
    public function patterns(): array
    {
        return $this->patterns;
    }

    /**
     * Fill rectangle with shading pattern.
     */
    public function fillRectWithPattern(float $x, float $y, float $w, float $h, string $patternName): self
    {
        $this->stream->fillRectWithPattern($x, $y, $w, $h, $patternName);

        return $this;
    }

    /**
     * Register a Type 1 tiling pattern; return resource name.
     */
    public function registerTilingPattern(PdfTilingPattern $pattern): string
    {
        $name = 'TP' . (++$this->tilingPatternCounter);
        $this->tilingPatterns[$name] = $pattern;

        return $name;
    }

    /**
     * @return array<string, PdfTilingPattern>
     *
     * @internal
     */
    public function tilingPatterns(): array
    {
        return $this->tilingPatterns;
    }

    /**
     * Fill rectangle with tiling pattern (same content stream ops
     * as shading pattern fill — Pattern color space via `/Pattern cs`).
     */
    public function fillRectWithTilingPattern(float $x, float $y, float $w, float $h, string $patternName): self
    {
        $this->stream->fillRectWithPattern($x, $y, $w, $h, $patternName);

        return $this;
    }

    /**
     * Set line dash pattern for subsequent stroke ops.
     *
     * @param  list<float>  $pattern  alternating on/off lengths
     */
    public function setLineDashPattern(array $pattern, float $phase = 0.0): self
    {
        $this->stream->setLineDashPattern($pattern, $phase);

        return $this;
    }

    /** Reset to solid line. */
    public function resetLineDashPattern(): self
    {
        $this->stream->resetLineDashPattern();

        return $this;
    }

    /** Set line cap (0=butt, 1=round, 2=projecting square). */
    public function setLineCap(int $cap): self
    {
        $this->stream->setLineCap($cap);

        return $this;
    }

    /** Set line join (0=miter, 1=round, 2=bevel). */
    public function setLineJoin(int $join): self
    {
        $this->stream->setLineJoin($join);

        return $this;
    }

    /** Set miter limit. */
    public function setMiterLimit(float $limit): self
    {
        $this->stream->setMiterLimit($limit);

        return $this;
    }

    /**
     * Set text rendering mode (0..7). See
     * ContentStream::setTextRenderingMode for the list of modes.
     */
    public function setTextRenderingMode(int $mode): self
    {
        $this->stream->setTextRenderingMode($mode);

        return $this;
    }

    /**
     * Set DeviceCMYK fill color for subsequent fill ops.
     * Effective until next color change or graphics state restore.
     */
    public function setCmykFillColor(float $c, float $m, float $y, float $k): self
    {
        $this->stream->setCmykFillColor($c, $m, $y, $k);

        return $this;
    }

    /**
     * Set DeviceCMYK stroke color.
     */
    public function setCmykStrokeColor(float $c, float $m, float $y, float $k): self
    {
        $this->stream->setCmykStrokeColor($c, $m, $y, $k);

        return $this;
    }

    /**
     * Clip subsequent drawing to a rectangle. Helper wraps
     * push/clip/draw/pop in one operation.
     */
    public function withClipRect(float $x, float $y, float $w, float $h, callable $draw): self
    {
        $this->stream->clipRect($x, $y, $w, $h);
        $draw($this);
        $this->stream->endClip();

        return $this;
    }

    /**
     * Clip subsequent drawing to an arbitrary polygon.
     *
     * @param  list<array{0: float, 1: float}>  $points
     */
    public function withClipPolygon(array $points, callable $draw): self
    {
        $this->stream->clipPolygon($points);
        $draw($this);
        $this->stream->endClip();

        return $this;
    }

    /**
     * Begin Optional Content section. Content emitted between
     * beginLayer/endLayer is wrapped in `/OC /MCn BDC ... EMC` so layer
     * visibility from /OCProperties toggles its rendering.
     */
    public function beginLayer(PdfLayer $layer): self
    {
        $name = $this->registerLayerProperty($layer);
        $this->stream->beginLayerContent($name);

        return $this;
    }

    /**
     * End Optional Content section. Must match preceding beginLayer.
     */
    public function endLayer(): self
    {
        $this->stream->endLayerContent();

        return $this;
    }

    /**
     * @return array<string, PdfLayer>
     *
     * @internal
     */
    public function layerProperties(): array
    {
        return $this->layerProperties;
    }

    private function registerLayerProperty(PdfLayer $layer): string
    {
        foreach ($this->layerProperties as $name => $l) {
            if ($l === $layer) {
                return $name;
            }
        }
        $name = 'OC' . (++$this->layerCounter);
        $this->layerProperties[$name] = $layer;

        return $name;
    }

    /**
     * Draw a reusable Form XObject at (x, y) scaled to (w, h).
     *
     * Emits `q sx 0 0 sy tx ty cm /Name Do Q`.
     * sx, sy chosen so that the form's /BBox maps to the requested rect.
     */
    public function useFormXObject(
        PdfFormXObject $form,
        float $x,
        float $y,
        float $widthPt,
        float $heightPt,
    ): self {
        $name = $this->registerFormXObject($form);
        $bw = $form->bboxWidth();
        $bh = $form->bboxHeight();
        $sx = $widthPt / $bw;
        $sy = $heightPt / $bh;
        $tx = $x - $form->bboxLlx * $sx;
        $ty = $y - $form->bboxLly * $sy;
        $this->stream->useFormXObject($name, $sx, $sy, $tx, $ty);

        return $this;
    }

    /**
     * @return array<string, PdfFormXObject>
     *
     * @internal
     */
    public function formXObjects(): array
    {
        return $this->formXObjects;
    }

    private function registerFormXObject(PdfFormXObject $form): string
    {
        foreach ($this->formXObjects as $name => $f) {
            if ($f === $form) {
                return $name;
            }
        }
        $name = 'Fm'.(++$this->formXObjectCounter);
        $this->formXObjects[$name] = $form;

        return $name;
    }

    /**
     * Separate fill/stroke opacity registration. Returns gs
     * resource name or null when both opacity values are >= 1.
     */
    public function maybeRegisterFillStrokeOpacityGs(?float $fillOpacity, ?float $strokeOpacity): ?string
    {
        $fill = ($fillOpacity !== null && $fillOpacity < 1.0) ? max(0.0, $fillOpacity) : null;
        $stroke = ($strokeOpacity !== null && $strokeOpacity < 1.0) ? max(0.0, $strokeOpacity) : null;
        if ($fill === null && $stroke === null) {
            return null;
        }

        return $this->registerExtGState(new PdfExtGState(fillOpacity: $fill, strokeOpacity: $stroke));
    }

    /**
     * Wrap arbitrary operations with ExtGState opacity through
     * push/pop graphics state. Use case — apply opacity to multiple draw
     * calls inside callable.
     *
     * @param  callable(): void  $draw
     */
    public function withOpacity(?float $fillOpacity, ?float $strokeOpacity, callable $draw): self
    {
        $gsName = $this->maybeRegisterFillStrokeOpacityGs($fillOpacity, $strokeOpacity);
        if ($gsName !== null) {
            $this->stream->pushGraphicsStateWithGs($gsName);
        }
        $draw();
        if ($gsName !== null) {
            $this->stream->popGraphicsState();
        }

        return $this;
    }

    /**
     * Filled rectangle (RGB 0..1).
     */
    public function fillRect(
        float $x, float $y, float $width, float $height,
        float $r = 0, float $g = 0, float $b = 0,
    ): self {
        $this->stream->fillRectangle($x, $y, $width, $height, $r, $g, $b);

        return $this;
    }

    /**
     * Filled rounded rectangle. radius=0 falls back to fillRect.
     */
    public function fillRoundedRect(
        float $x, float $y, float $width, float $height, float $radius,
        float $r = 0, float $g = 0, float $b = 0,
    ): self {
        if ($radius <= 0) {
            return $this->fillRect($x, $y, $width, $height, $r, $g, $b);
        }
        $this->stream->fillRoundedRectangle($x, $y, $width, $height, $radius, $r, $g, $b);

        return $this;
    }

    /**
     * Stroked rounded rectangle.
     */
    public function strokeRoundedRect(
        float $x, float $y, float $width, float $height, float $radius,
        float $lineWidthPt = 0.5,
        float $r = 0, float $g = 0, float $b = 0,
    ): self {
        if ($radius <= 0) {
            return $this->strokeRect($x, $y, $width, $height, $lineWidthPt, $r, $g, $b);
        }
        $this->stream->strokeRoundedRectangle($x, $y, $width, $height, $radius, $lineWidthPt, $r, $g, $b);

        return $this;
    }

    /**
     * Outline rectangle (RGB 0..1).
     */
    public function strokeRect(
        float $x, float $y, float $width, float $height,
        float $lineWidthPt = 0.5,
        float $r = 0, float $g = 0, float $b = 0,
    ): self {
        $this->stream->strokeRectangle($x, $y, $width, $height, $lineWidthPt, $r, $g, $b);

        return $this;
    }

    /**
     * Tagged PDF — emit BDC/EMC pair around content.
     */
    public function beginMarkedContent(string $tag, int $mcid): self
    {
        $this->stream->emitBeginMarkedContent($tag, $mcid);

        return $this;
    }

    public function endMarkedContent(): self
    {
        $this->stream->emitEndMarkedContent();

        return $this;
    }

    /**
     * Begin /Artifact marked content (PDF/UA — content
     * excluded from struct tree).
     */
    public function beginArtifact(string $type = 'Pagination'): self
    {
        $this->stream->emitBeginArtifact($type);

        return $this;
    }

    /**
     * Stroked straight line.
     */
    public function strokeLine(
        float $x1, float $y1, float $x2, float $y2,
        float $lineWidthPt = 0.5,
        float $r = 0, float $g = 0, float $b = 0,
    ): self {
        $this->stream->strokeLine($x1, $y1, $x2, $y2, $lineWidthPt, $r, $g, $b);

        return $this;
    }

    /**
     * Filled polygon (closed path).
     *
     * @param  list<array{0: float, 1: float}>  $points
     */
    public function fillPolygon(array $points, float $r = 0, float $g = 0, float $b = 0): self
    {
        $this->stream->fillPolygon($points, $r, $g, $b);

        return $this;
    }

    /**
     * Stroked polyline (not closed).
     *
     * @param  list<array{0: float, 1: float}>  $points
     */
    public function strokePolyline(
        array $points,
        float $lineWidthPt = 1.0,
        float $r = 0, float $g = 0, float $b = 0,
    ): self {
        $this->stream->strokePolyline($points, $lineWidthPt, $r, $g, $b);

        return $this;
    }

    /**
     * Emit generic path (with cubic Bezier curves).
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
        $this->stream->emitPath($commands, $mode, $fillRgb, $strokeRgb, $lineWidthPt);

        return $this;
    }

    /**
     * Draw image (PNG/JPEG) at (x, y) with scaling to (widthPt, heightPt).
     */
    public function drawImage(PdfImage $image, float $x, float $y, float $widthPt, float $heightPt): self
    {
        $resourceName = $this->registerImage($image);
        $this->stream->drawImage($resourceName, $x, $y, $widthPt, $heightPt);

        return $this;
    }

    /**
     * drawImage with rotation around image center (counter-clockwise radians).
     */
    public function drawImageRotated(
        PdfImage $image, float $x, float $y, float $widthPt, float $heightPt, float $angleRad,
    ): self {
        $resourceName = $this->registerImage($image);
        $this->stream->drawImageRotated($resourceName, $x, $y, $widthPt, $heightPt, $angleRad);

        return $this;
    }

    /**
     * drawImage with opacity. opacity ∈ (0, 1) — fill alpha
     * via ExtGState `/ca`. 1.0 is equivalent to plain drawImage.
     */
    public function drawImageWithOpacity(
        PdfImage $image,
        float $x,
        float $y,
        float $widthPt,
        float $heightPt,
        float $opacity,
    ): self {
        $resourceName = $this->registerImage($image);
        if ($opacity >= 1.0) {
            $this->stream->drawImage($resourceName, $x, $y, $widthPt, $heightPt);

            return $this;
        }
        $gsName = $this->registerExtGState(new PdfExtGState(fillOpacity: max(0.0, $opacity)));
        $this->stream->drawImageWithGs($resourceName, $gsName, $x, $y, $widthPt, $heightPt);

        return $this;
    }

    /**
     * External URL link — click inside Rect opens $uri.
     */
    public function addExternalLink(float $x, float $y, float $width, float $height, string $uri): self
    {
        $this->linkAnnotations[] = [
            'kind' => 'uri',
            'x1' => $x, 'y1' => $y,
            'x2' => $x + $width, 'y2' => $y + $height,
            'target' => $uri,
        ];

        return $this;
    }

    /** @var list<array<string, mixed>> markup annotations (Text/Highlight/Underline/StrikeOut/FreeText). */
    private array $markupAnnotations = [];

    /**
     * Sticky note text annotation. Click shows popup with $contents.
     *
     * @param  string  $icon  one of Comment|Note|Help|NewParagraph|Paragraph|Insert (default Note)
     * @param  array{0:float,1:float,2:float}|null  $color  RGB 0..1
     */
    public function addTextAnnotation(
        float $x,
        float $y,
        string $contents,
        ?string $title = null,
        string $icon = 'Note',
        ?array $color = null,
    ): self {
        $valid = ['Comment', 'Note', 'Help', 'NewParagraph', 'Paragraph', 'Insert', 'Key'];
        if (! in_array($icon, $valid, true)) {
            throw new \InvalidArgumentException('Invalid text annotation icon: ' . $icon);
        }
        $this->markupAnnotations[] = [
            'kind' => 'text',
            'x1' => $x, 'y1' => $y,
            'x2' => $x + 18, 'y2' => $y + 18,
            'contents' => $contents,
            'title' => $title,
            'icon' => $icon,
            'color' => $color,
        ];

        return $this;
    }

    /**
     * Highlight markup annotation over rect.
     *
     * @param  array{0:float,1:float,2:float}|null  $color  default yellow (1,1,0)
     */
    public function addHighlightAnnotation(
        float $x,
        float $y,
        float $width,
        float $height,
        string $contents = '',
        ?array $color = null,
    ): self {
        return $this->addQuadMarkup('highlight', $x, $y, $width, $height, $contents, $color ?? [1.0, 1.0, 0.0]);
    }

    /**
     * Underline markup annotation.
     *
     * @param  array{0:float,1:float,2:float}|null  $color  default blue (0, 0.5, 1)
     */
    public function addUnderlineAnnotation(
        float $x,
        float $y,
        float $width,
        float $height,
        string $contents = '',
        ?array $color = null,
    ): self {
        return $this->addQuadMarkup('underline', $x, $y, $width, $height, $contents, $color ?? [0.0, 0.5, 1.0]);
    }

    /**
     * Strike-out markup annotation.
     *
     * @param  array{0:float,1:float,2:float}|null  $color  default red (1, 0, 0)
     */
    public function addStrikeOutAnnotation(
        float $x,
        float $y,
        float $width,
        float $height,
        string $contents = '',
        ?array $color = null,
    ): self {
        return $this->addQuadMarkup('strikeout', $x, $y, $width, $height, $contents, $color ?? [1.0, 0.0, 0.0]);
    }

    /**
     * Square (rectangle) annotation — outlines a region.
     *
     * @param  array{0:float,1:float,2:float}|null  $strokeColor  /C border
     * @param  array{0:float,1:float,2:float}|null  $fillColor    /IC interior
     */
    public function addSquareAnnotation(
        float $x,
        float $y,
        float $width,
        float $height,
        ?array $strokeColor = null,
        ?array $fillColor = null,
        float $borderWidth = 1.0,
    ): self {
        $this->markupAnnotations[] = [
            'kind' => 'square',
            'x1' => $x, 'y1' => $y,
            'x2' => $x + $width, 'y2' => $y + $height,
            'contents' => '',
            'color' => $strokeColor,
            'fillColor' => $fillColor,
            'borderWidth' => $borderWidth,
        ];

        return $this;
    }

    /**
     * Circle (ellipse) annotation — outlines an oval inscribed
     * within (x, y, width, height) rect.
     *
     * @param  array{0:float,1:float,2:float}|null  $strokeColor  /C border
     * @param  array{0:float,1:float,2:float}|null  $fillColor    /IC interior
     */
    public function addCircleAnnotation(
        float $x,
        float $y,
        float $width,
        float $height,
        ?array $strokeColor = null,
        ?array $fillColor = null,
        float $borderWidth = 1.0,
    ): self {
        $this->markupAnnotations[] = [
            'kind' => 'circle',
            'x1' => $x, 'y1' => $y,
            'x2' => $x + $width, 'y2' => $y + $height,
            'contents' => '',
            'color' => $strokeColor,
            'fillColor' => $fillColor,
            'borderWidth' => $borderWidth,
        ];

        return $this;
    }

    /**
     * Stamp annotation — predefined rubber-stamp icon.
     *
     * @param  string  $stampName  one of Approved, Confidential, Draft,
     *                             Experimental, Expired, Final, ForComment,
     *                             ForPublicRelease, NotApproved, NotForPublicRelease,
     *                             Sold, TopSecret.
     */
    public function addStampAnnotation(
        float $x,
        float $y,
        float $width,
        float $height,
        string $stampName = 'Draft',
        string $contents = '',
    ): self {
        $valid = [
            'Approved', 'Confidential', 'Draft', 'Experimental', 'Expired',
            'Final', 'ForComment', 'ForPublicRelease', 'NotApproved',
            'NotForPublicRelease', 'Sold', 'TopSecret',
        ];
        if (! in_array($stampName, $valid, true)) {
            throw new \InvalidArgumentException('Invalid stamp name: ' . $stampName);
        }
        $this->markupAnnotations[] = [
            'kind' => 'stamp',
            'x1' => $x, 'y1' => $y,
            'x2' => $x + $width, 'y2' => $y + $height,
            'contents' => $contents,
            'stampName' => $stampName,
        ];

        return $this;
    }

    /**
     * Ink annotation — freehand drawing with multiple pen strokes.
     *
     * @param  list<list<array{0:float,1:float}>>  $strokes  outer list = strokes (pen-down spans);
     *                                                       inner list = (x, y) points per stroke
     * @param  array{0:float,1:float,2:float}|null  $strokeColor
     */
    public function addInkAnnotation(
        array $strokes,
        ?array $strokeColor = null,
        float $borderWidth = 1.0,
        string $contents = '',
    ): self {
        if ($strokes === []) {
            throw new \InvalidArgumentException('Ink annotation needs ≥1 stroke');
        }
        // Compute global bbox over all strokes.
        $xs = [];
        $ys = [];
        foreach ($strokes as $stroke) {
            if ($stroke === []) {
                throw new \InvalidArgumentException('Ink stroke must have ≥1 point');
            }
            foreach ($stroke as [$x, $y]) {
                $xs[] = $x;
                $ys[] = $y;
            }
        }
        $this->markupAnnotations[] = [
            'kind' => 'ink',
            'x1' => min($xs), 'y1' => min($ys),
            'x2' => max($xs), 'y2' => max($ys),
            'contents' => $contents,
            'color' => $strokeColor,
            'inkStrokes' => $strokes,
            'borderWidth' => $borderWidth,
        ];

        return $this;
    }

    /**
     * Polygon annotation — closed shape with vertex list.
     *
     * @param  list<array{0:float,1:float}>  $vertices
     * @param  array{0:float,1:float,2:float}|null  $strokeColor
     * @param  array{0:float,1:float,2:float}|null  $fillColor
     */
    public function addPolygonAnnotation(
        array $vertices,
        ?array $strokeColor = null,
        ?array $fillColor = null,
        float $borderWidth = 1.0,
    ): self {
        if (count($vertices) < 3) {
            throw new \InvalidArgumentException('Polygon annotation needs ≥3 vertices');
        }

        return $this->addPolyMarkup('polygon', $vertices, $strokeColor, $fillColor, $borderWidth);
    }

    /**
     * PolyLine annotation — open polyline.
     *
     * @param  list<array{0:float,1:float}>  $vertices
     * @param  array{0:float,1:float,2:float}|null  $strokeColor
     */
    public function addPolyLineAnnotation(
        array $vertices,
        ?array $strokeColor = null,
        float $borderWidth = 1.0,
    ): self {
        if (count($vertices) < 2) {
            throw new \InvalidArgumentException('PolyLine annotation needs ≥2 vertices');
        }

        return $this->addPolyMarkup('polyline', $vertices, $strokeColor, null, $borderWidth);
    }

    /**
     * @param  list<array{0:float,1:float}>  $vertices
     * @param  array{0:float,1:float,2:float}|null  $stroke
     * @param  array{0:float,1:float,2:float}|null  $fill
     */
    private function addPolyMarkup(string $kind, array $vertices, ?array $stroke, ?array $fill, float $borderWidth): self
    {
        $xs = array_column($vertices, 0);
        $ys = array_column($vertices, 1);
        $this->markupAnnotations[] = [
            'kind' => $kind,
            'x1' => min($xs), 'y1' => min($ys),
            'x2' => max($xs), 'y2' => max($ys),
            'contents' => '',
            'color' => $stroke,
            'fillColor' => $fill,
            'vertices' => $vertices,
            'borderWidth' => $borderWidth,
        ];

        return $this;
    }

    /**
     * Line annotation — emits a thin line between two endpoints.
     *
     * @param  array{0:float,1:float,2:float}|null  $strokeColor
     */
    public function addLineAnnotation(
        float $x1,
        float $y1,
        float $x2,
        float $y2,
        ?array $strokeColor = null,
        float $borderWidth = 1.0,
    ): self {
        $this->markupAnnotations[] = [
            'kind' => 'line',
            'x1' => min($x1, $x2), 'y1' => min($y1, $y2),
            'x2' => max($x1, $x2), 'y2' => max($y1, $y2),
            'contents' => '',
            'color' => $strokeColor,
            'lineX1' => $x1, 'lineY1' => $y1,
            'lineX2' => $x2, 'lineY2' => $y2,
            'borderWidth' => $borderWidth,
        ];

        return $this;
    }

    /**
     * FreeText annotation — text rendered directly on the page surface.
     *
     * @param  array{0:float,1:float,2:float}|null  $color
     */
    public function addFreeTextAnnotation(
        float $x,
        float $y,
        float $width,
        float $height,
        string $text,
        ?array $color = null,
        float $fontSize = 11.0,
    ): self {
        $this->markupAnnotations[] = [
            'kind' => 'freetext',
            'x1' => $x, 'y1' => $y,
            'x2' => $x + $width, 'y2' => $y + $height,
            'contents' => $text,
            'color' => $color,
            'fontSize' => $fontSize,
        ];

        return $this;
    }

    /**
     * @param  array{0:float,1:float,2:float}  $color
     */
    private function addQuadMarkup(string $kind, float $x, float $y, float $w, float $h, string $contents, array $color): self
    {
        $this->markupAnnotations[] = [
            'kind' => $kind,
            'x1' => $x, 'y1' => $y,
            'x2' => $x + $w, 'y2' => $y + $h,
            'contents' => $contents,
            'color' => $color,
        ];

        return $this;
    }

    /**
     * @return list<array<string, mixed>>
     *
     * @internal
     */
    public function markupAnnotations(): array
    {
        return $this->markupAnnotations;
    }

    /**
     * Internal link — click inside Rect jumps to named destination $destName.
     */
    public function addInternalLink(float $x, float $y, float $width, float $height, string $destName): self
    {
        $this->linkAnnotations[] = [
            'kind' => 'internal',
            'x1' => $x, 'y1' => $y,
            'x2' => $x + $width, 'y2' => $y + $height,
            'target' => $destName,
        ];

        return $this;
    }

    /**
     * Named navigation link — click triggers reader action.
     *
     * @param  string  $action  one of: NextPage, PrevPage, FirstPage, LastPage,
     *                          Find, Print, SaveAs, GoBack, GoForward.
     */
    public function addNamedActionLink(float $x, float $y, float $width, float $height, string $action): self
    {
        $valid = ['NextPage', 'PrevPage', 'FirstPage', 'LastPage', 'Find', 'Print', 'SaveAs', 'GoBack', 'GoForward'];
        if (! in_array($action, $valid, true)) {
            throw new \InvalidArgumentException('Invalid named action: ' . $action);
        }
        $this->linkAnnotations[] = [
            'kind' => 'named',
            'x1' => $x, 'y1' => $y,
            'x2' => $x + $width, 'y2' => $y + $height,
            'target' => $action,
        ];

        return $this;
    }

    /**
     * JavaScript link — click executes $script.
     */
    public function addJavaScriptLink(float $x, float $y, float $width, float $height, string $script): self
    {
        $this->linkAnnotations[] = [
            'kind' => 'javascript',
            'x1' => $x, 'y1' => $y,
            'x2' => $x + $width, 'y2' => $y + $height,
            'target' => $script,
        ];

        return $this;
    }

    /**
     * Launch link — click opens external file (e.g. companion .docx).
     *
     * Note: most PDF readers disable launch actions by default for security.
     */
    public function addLaunchLink(float $x, float $y, float $width, float $height, string $filePath): self
    {
        $this->linkAnnotations[] = [
            'kind' => 'launch',
            'x1' => $x, 'y1' => $y,
            'x2' => $x + $width, 'y2' => $y + $height,
            'target' => $filePath,
        ];

        return $this;
    }

    /**
     * @return list<array{kind: 'uri'|'internal', x1: float, y1: float, x2: float, y2: float, target: string}>
     *
     * @internal
     */
    public function linkAnnotations(): array
    {
        return $this->linkAnnotations;
    }

    /**
     * Add interactive form field widget on the page.
     *
     * @param  list<string>  $options  for combo/list/radio-group
     * @param  list<array{x: float, y: float, w: float, h: float}>  $radioWidgets  for radio-group (one widget per option)
     */
    public function addFormField(
        string $type,
        string $name,
        float $x,
        float $y,
        float $width,
        float $height,
        string $defaultValue = '',
        ?string $tooltip = null,
        bool $required = false,
        bool $readOnly = false,
        array $options = [],
        array $radioWidgets = [],
        ?string $validateScript = null,
        ?string $calculateScript = null,
        ?string $formatScript = null,
        ?string $keystrokeScript = null,
        ?string $buttonCaption = null,
        ?string $submitUrl = null,
        ?string $clickScript = null,
    ): self {
        $this->formFields[] = [
            'type' => $type,
            'name' => $name,
            'x' => $x,
            'y' => $y,
            'w' => $width,
            'h' => $height,
            'defaultValue' => $defaultValue,
            'tooltip' => $tooltip,
            'required' => $required,
            'readOnly' => $readOnly,
            'options' => $options,
            'radioWidgets' => $radioWidgets,
            'validateScript' => $validateScript,
            'calculateScript' => $calculateScript,
            'formatScript' => $formatScript,
            'keystrokeScript' => $keystrokeScript,
            'buttonCaption' => $buttonCaption,
            'submitUrl' => $submitUrl,
            'clickScript' => $clickScript,
        ];

        return $this;
    }

    /**
     * @return list<array<string, mixed>>
     *
     * @internal
     */
    public function formFields(): array
    {
        return $this->formFields;
    }

    /**
     * Low-level escape hatch — append raw content stream commands.
     * Used for unsupported operators (Bezier curves, text matrices,
     * manual graphics state save/restore). Caller is responsible for
     * valid PDF syntax.
     */
    public function rawContentStream(): ContentStream
    {
        return $this->stream;
    }

    /**
     * Build content stream body for emission into the Writer.
     *
     * @internal Used by Document.
     */
    public function buildContentStream(): string
    {
        return $this->stream->toString();
    }

    /**
     * Resource map: standard fonts (resourceName → StandardFont).
     *
     * @return array<string, StandardFont>
     *
     * @internal
     */
    public function standardFonts(): array
    {
        return $this->standardFonts;
    }

    /**
     * @return array<string, PdfFont>
     *
     * @internal
     */
    public function embeddedFonts(): array
    {
        return $this->embeddedFonts;
    }

    /**
     * @return array<string, PdfImage>
     *
     * @internal
     */
    public function images(): array
    {
        return $this->images;
    }

    /**
     * Register standard font for use on this page. Returns resource name.
     * If the font is already registered, returns the existing name.
     */
    private function registerStandardFont(StandardFont $font): string
    {
        foreach ($this->standardFonts as $name => $f) {
            if ($f === $font) {
                return $name;
            }
        }
        $name = 'F'.(++$this->fontCounter);
        $this->standardFonts[$name] = $font;

        return $name;
    }

    private function registerEmbeddedFont(PdfFont $font): string
    {
        foreach ($this->embeddedFonts as $name => $f) {
            if ($f === $font) {
                return $name;
            }
        }
        $name = 'F'.(++$this->fontCounter);
        $this->embeddedFonts[$name] = $font;

        return $name;
    }

    private function registerImage(PdfImage $image): string
    {
        foreach ($this->images as $name => $img) {
            if ($img === $image) {
                return $name;
            }
        }
        $name = 'Im'.(++$this->imageCounter);
        $this->images[$name] = $image;

        return $name;
    }

    /**
     * Register ExtGState (opacity). Dedup by key() —
     * identical opacity tuples reuse the same resource.
     *
     * @return string Resource name (`Gs1`, `Gs2`, ...).
     */
    public function registerExtGState(PdfExtGState $state): string
    {
        $key = $state->key();
        foreach ($this->extGStates as $name => $existing) {
            if ($existing->key() === $key) {
                return $name;
            }
        }
        $name = 'Gs'.(++$this->extGStateCounter);
        $this->extGStates[$name] = $state;

        return $name;
    }

    /**
     * @return array<string, PdfExtGState>
     *
     * @internal
     */
    public function extGStates(): array
    {
        return $this->extGStates;
    }
}
