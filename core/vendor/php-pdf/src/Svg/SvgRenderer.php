<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Svg;

use Dskripchenko\PhpPdf\Pdf\Page;

/**
 * SVG → PDF native paths renderer.
 *
 * Parses SVG XML and emits PDF drawing operators onto a target Page region
 * (x, y, width, height in PDF coords; SVG coords transformed with Y-flip +
 * scaled).
 *
 * SVG namespace ignored — works on any XML with SVG-named elements.
 */
final class SvgRenderer
{
    /**
     * @return array{0: int, 1: int, 2: int}|null
     */
    private static function namedColorRgb(string $name): ?array
    {
        $named = [
            'black' => [0, 0, 0], 'white' => [255, 255, 255],
            'red' => [255, 0, 0], 'green' => [0, 128, 0], 'blue' => [0, 0, 255],
            'gray' => [128, 128, 128], 'silver' => [192, 192, 192],
            'maroon' => [128, 0, 0], 'olive' => [128, 128, 0], 'lime' => [0, 255, 0],
            'aqua' => [0, 255, 255], 'teal' => [0, 128, 128], 'navy' => [0, 0, 128],
            'fuchsia' => [255, 0, 255], 'purple' => [128, 0, 128], 'yellow' => [255, 255, 0],
        ];

        return $named[strtolower($name)] ?? null;
    }

    /**
     * Parse opacity attribute (0..1 float).
     * Returns 1.0 if null or out-of-range.
     */
    private static function parseOpacity(\SimpleXMLElement $el, string $attr, float $multiplier = 1.0): float
    {
        $val = $el[$attr] ?? null;
        if ($val === null) {
            return $multiplier;
        }
        $f = (float) (string) $val;
        if ($f < 0) {
            $f = 0;
        }
        if ($f > 1) {
            $f = 1;
        }

        return $f * $multiplier;
    }

    /**
     * Parse fill/stroke value → [r, g, b, hasColor]. hasColor=false for 'none'.
     *
     * @return array{0: float, 1: float, 2: float, 3: bool}
     */
    private static function parseColor(?string $value): array
    {
        if ($value === null || $value === '' || strtolower($value) === 'none') {
            return [0, 0, 0, false];
        }
        $v = trim($value);
        if (str_starts_with($v, '#')) {
            $hex = substr($v, 1);
            if (strlen($hex) === 3) {
                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
            }
            if (strlen($hex) === 6 && ctype_xdigit($hex)) {
                return [
                    hexdec(substr($hex, 0, 2)) / 255,
                    hexdec(substr($hex, 2, 2)) / 255,
                    hexdec(substr($hex, 4, 2)) / 255,
                    true,
                ];
            }
        }
        $named = self::namedColorRgb($v);
        if ($named !== null) {
            return [$named[0] / 255, $named[1] / 255, $named[2] / 255, true];
        }

        return [0, 0, 0, true]; // unknown → black.
    }

    /**
     * Parse SVG <style> block content → CSS rules.
     *
     * Supports tag selectors (rect, circle), class (.foo), id (#bar).
     * Selectors separated by `,` — applied to each independently.
     *
     * @return list<array{selector: string, type: string, name: string, declarations: array<string, string>}>
     */
    private static function parseCssRules(string $css): array
    {
        $rules = [];
        $css = preg_replace('@/\*.*?\*/@s', '', $css) ?? $css; // strip comments
        if (! preg_match_all('@([^{}]+)\{([^}]+)\}@', $css, $matches, PREG_SET_ORDER)) {
            return [];
        }
        foreach ($matches as $m) {
            $selectorList = $m[1];
            $declarationsStr = trim($m[2]);
            $declarations = [];
            foreach (explode(';', $declarationsStr) as $decl) {
                $decl = trim($decl);
                if ($decl === '' || ! str_contains($decl, ':')) {
                    continue;
                }
                [$k, $v] = explode(':', $decl, 2);
                $declarations[trim($k)] = trim($v);
            }
            foreach (explode(',', $selectorList) as $sel) {
                $sel = trim($sel);
                if ($sel === '') {
                    continue;
                }
                if (str_starts_with($sel, '.')) {
                    $rules[] = ['selector' => $sel, 'type' => 'class', 'name' => substr($sel, 1), 'declarations' => $declarations];
                } elseif (str_starts_with($sel, '#')) {
                    $rules[] = ['selector' => $sel, 'type' => 'id', 'name' => substr($sel, 1), 'declarations' => $declarations];
                } else {
                    $rules[] = ['selector' => $sel, 'type' => 'tag', 'name' => $sel, 'declarations' => $declarations];
                }
            }
        }

        return $rules;
    }

    /**
     * Apply matching CSS rules to a SimpleXMLElement (mutate attributes
     * for those not yet set). Specificity: id > class > tag.
     *
     * @param  list<array<string, mixed>>  $rules
     */
    private static function applyCssRules(\SimpleXMLElement $el, array $rules): void
    {
        $tag = $el->getName();
        $class = (string) ($el['class'] ?? '');
        $id = (string) ($el['id'] ?? '');
        $classes = preg_split('@\s+@', $class) ?: [];

        // Tag selectors first (lowest specificity).
        foreach ($rules as $rule) {
            if ($rule['type'] === 'tag' && $rule['name'] === $tag) {
                self::applyDeclarations($el, $rule['declarations']);
            }
        }
        // Class selectors.
        foreach ($rules as $rule) {
            if ($rule['type'] === 'class' && in_array($rule['name'], $classes, true)) {
                self::applyDeclarations($el, $rule['declarations']);
            }
        }
        // Id selectors (highest specificity per spec, but inline attributes still win).
        foreach ($rules as $rule) {
            if ($rule['type'] === 'id' && $rule['name'] === $id) {
                self::applyDeclarations($el, $rule['declarations']);
            }
        }
    }

    /**
     * @param  array<string, string>  $declarations
     */
    private static function applyDeclarations(\SimpleXMLElement $el, array $declarations): void
    {
        foreach ($declarations as $prop => $value) {
            // Don't override inline-set attributes (specificity rule).
            if (isset($el[$prop])) {
                continue;
            }
            $el->addAttribute($prop, $value);
        }
    }

    public static function render(string $svgXml, Page $page, float $boxX, float $boxY, float $boxW, float $boxH): void
    {
        // SimpleXML's xpath() cannot address the default namespace without
        // a registered prefix, which silently disabled every //style,
        // //linearGradient, //radialGradient, //use lookup for real-world
        // SVGs (they all declare xmlns="http://www.w3.org/2000/svg").
        // Dropping the default-namespace declaration keeps element and
        // attribute access uniform across namespaced and plain input.
        $svgXml = preg_replace('@\sxmlns="http://www\.w3\.org/2000/svg"@', '', $svgXml) ?? $svgXml;

        // Suppress libxml warnings.
        $prev = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($svgXml);
        libxml_use_internal_errors($prev);
        if ($xml === false) {
            return;
        }

        // Determine source coord box.
        $srcW = (float) ($xml['width'] ?? 100);
        $srcH = (float) ($xml['height'] ?? 100);
        if (isset($xml['viewBox'])) {
            $parts = preg_split('@\s+@', trim((string) $xml['viewBox']));
            if ($parts !== false && count($parts) >= 4) {
                $srcW = (float) $parts[2];
                $srcH = (float) $parts[3];
            }
        }
        if ($srcW <= 0) {
            $srcW = 100;
        }
        if ($srcH <= 0) {
            $srcH = 100;
        }

        $scaleX = $boxW / $srcW;
        $scaleY = $boxH / $srcH;

        // Transform svg(x, y) → pdf(x, y). SVG Y grows down; PDF Y grows up.
        $tx = function (float $svgX) use ($boxX, $scaleX): float {
            return $boxX + $svgX * $scaleX;
        };
        $ty = function (float $svgY) use ($boxY, $boxH, $scaleY): float {
            return $boxY + ($boxH - $svgY * $scaleY);
        };

        // Collect CSS rules from <style> elements + apply to all
        // elements recursively before rendering.
        $cssRules = [];
        foreach ($xml->xpath('//style') ?: [] as $styleEl) {
            $cssRules = array_merge($cssRules, self::parseCssRules((string) $styleEl));
        }
        if ($cssRules !== []) {
            self::applyCssToTree($xml, $cssRules);
        }

        // Resolve <use> references to <defs> children.
        self::resolveUseReferences($xml);

        // Collect linearGradient definitions (id → params).
        $gradients = self::parseGradients($xml);

        self::walkElement($xml, $page, $tx, $ty, $scaleX, $scaleY, $gradients);
    }

    /**
     * Parse all <linearGradient> elements in SVG → id-keyed map.
     *
     * @return array<string, array{type: 'linear', x1: float, y1: float, x2: float, y2: float, stops: list<array{offset: float, color: array{0:float,1:float,2:float}}>}>
     */
    private static function parseGradients(\SimpleXMLElement $root): array
    {
        $out = [];
        foreach ($root->xpath('//linearGradient') ?: [] as $g) {
            $id = (string) ($g['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $x1 = self::parsePctOrFloat((string) ($g['x1'] ?? '0'));
            $y1 = self::parsePctOrFloat((string) ($g['y1'] ?? '0'));
            $x2 = self::parsePctOrFloat((string) ($g['x2'] ?? '1'));
            $y2 = self::parsePctOrFloat((string) ($g['y2'] ?? '0'));
            $stops = self::parseGradientStops($g);
            // gradientTransform attribute.
            $gradTransform = isset($g['gradientTransform'])
                ? self::parseTransform((string) $g['gradientTransform'])
                : null;
            $out[$id] = [
                'type' => 'linear',
                'x1' => $x1, 'y1' => $y1, 'x2' => $x2, 'y2' => $y2,
                'stops' => $stops,
                'transform' => $gradTransform,
            ];
        }
        // radialGradient parsing.
        foreach ($root->xpath('//radialGradient') ?: [] as $g) {
            $id = (string) ($g['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $cx = self::parsePctOrFloat((string) ($g['cx'] ?? '0.5'));
            $cy = self::parsePctOrFloat((string) ($g['cy'] ?? '0.5'));
            $r = self::parsePctOrFloat((string) ($g['r'] ?? '0.5'));
            $fx = isset($g['fx']) ? self::parsePctOrFloat((string) $g['fx']) : $cx;
            $fy = isset($g['fy']) ? self::parsePctOrFloat((string) $g['fy']) : $cy;
            $stops = self::parseGradientStops($g);
            $gradTransform = isset($g['gradientTransform'])
                ? self::parseTransform((string) $g['gradientTransform'])
                : null;
            $out[$id] = [
                'type' => 'radial',
                'cx' => $cx, 'cy' => $cy, 'r' => $r, 'fx' => $fx, 'fy' => $fy,
                'stops' => $stops,
                'transform' => $gradTransform,
            ];
        }

        return $out;
    }

    /**
     * @return list<array{offset: float, color: array{0: float, 1: float, 2: float}}>
     */
    private static function parseGradientStops(\SimpleXMLElement $gradient): array
    {
        $stops = [];
        foreach ($gradient->children() as $stop) {
            if ($stop->getName() !== 'stop') {
                continue;
            }
            $offset = self::parsePctOrFloat((string) ($stop['offset'] ?? '0'));
            $stopColorAttr = (string) ($stop['stop-color'] ?? '#000');
            $style = (string) ($stop['style'] ?? '');
            if ($style !== '' && preg_match('@stop-color\s*:\s*([^;]+)@', $style, $m)) {
                $stopColorAttr = trim($m[1]);
            }
            [$r, $g_, $b, $hasColor] = self::parseColor($stopColorAttr);
            $stops[] = ['offset' => $offset, 'color' => [$r, $g_, $b]];
        }

        return $stops;
    }

    private static function parsePctOrFloat(string $s): float
    {
        $s = trim($s);
        if (str_ends_with($s, '%')) {
            return ((float) substr($s, 0, -1)) / 100;
        }

        return (float) $s;
    }

    /**
     * Walk dom, replace each <use> with a deep-clone of the referenced
     * element. Both xlink:href and href are supported.
     */
    private static function resolveUseReferences(\SimpleXMLElement $root): void
    {
        // Build id → element map.
        $idMap = [];
        foreach ($root->xpath('//*[@id]') ?: [] as $el) {
            $idMap[(string) $el['id']] = $el;
        }
        if ($idMap === []) {
            return;
        }
        // Find all use elements.
        $uses = $root->xpath('//use') ?: [];
        foreach ($uses as $use) {
            $href = (string) ($use['href'] ?? $use->attributes('xlink', true)?->href ?? '');
            if ($href === '' || ! str_starts_with($href, '#')) {
                continue;
            }
            $refId = substr($href, 1);
            if (! isset($idMap[$refId])) {
                continue;
            }
            $ref = $idMap[$refId];
            // Replace tag content in-place: convert <use> into cloned ref tag.
            $newName = $ref->getName();
            $dom = dom_import_simplexml($use);
            $refDom = dom_import_simplexml($ref);
            $clone = $refDom->cloneNode(true);
            // Rename use → ref tag by replacing element.
            $newEl = $dom->ownerDocument->createElement($newName);
            // Copy attributes from ref (cloned) — first.
            if ($clone instanceof \DOMElement) {
                foreach ($clone->attributes as $attr) {
                    /** @var \DOMAttr $attr */
                    if ($attr->name === 'id') {
                        continue;
                    }
                    $newEl->setAttribute($attr->name, $attr->value);
                }
                // Copy child nodes.
                foreach ($clone->childNodes as $childNode) {
                    $newEl->appendChild($childNode->cloneNode(true));
                }
            }
            // Override with use's attributes (use x/y/transform overrides ref's).
            foreach ($dom->attributes as $attr) {
                /** @var \DOMAttr $attr */
                if (in_array($attr->name, ['href', 'xlink:href'], true)) {
                    continue;
                }
                $newEl->setAttribute($attr->name, $attr->value);
            }
            $dom->parentNode?->replaceChild($newEl, $dom);
        }
    }

    /**
     * Recursively apply CSS rules to all child elements.
     *
     * @param  list<array<string, mixed>>  $rules
     */
    private static function applyCssToTree(\SimpleXMLElement $el, array $rules): void
    {
        foreach ($el->children() as $child) {
            self::applyCssRules($child, $rules);
            self::applyCssToTree($child, $rules);
        }
    }

    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $currentGradients = [];

    /**
     * @param  callable(float): float  $tx
     * @param  callable(float): float  $ty
     * @param  array<string, array<string, mixed>>  $gradients
     */
    private static function walkElement(\SimpleXMLElement $el, Page $page, callable $tx, callable $ty, float $scaleX, float $scaleY, array $gradients = []): void
    {
        self::$currentGradients = $gradients;
        foreach ($el->children() as $child) {
            // Apply element-level transform (if present).
            $elTx = $tx;
            $elTy = $ty;
            if (isset($child['transform'])) {
                $matrix = self::parseTransform((string) $child['transform']);
                if ($matrix !== null) {
                    [$a, $b, $c, $d, $e, $f] = $matrix;
                    $origTx = $tx;
                    $origTy = $ty;
                    $elTx = static fn (float $x, float $y = 0) => $origTx($a * $x + $c * $y + $e);
                    $elTy = static fn (float $y, float $x = 0) => $origTy($b * $x + $d * $y + $f);
                    // Pre-compose transformation: we need exact (x, y) inputs.
                    // Since existing API uses tx(x) and ty(y) separately (no
                    // cross-coupling), we instead apply the transform inline in each
                    // shape via wrapper closures that take (x, y) → (px, py).
                    self::walkChildTransformed($child, $page, $tx, $ty, $matrix, $scaleX, $scaleY);

                    continue;
                }
            }
            $tag = $child->getName();
            match ($tag) {
                'rect' => self::renderRect($child, $page, $elTx, $elTy, $scaleX, $scaleY),
                'line' => self::renderLine($child, $page, $elTx, $elTy, $scaleX),
                'circle' => self::renderCircle($child, $page, $elTx, $elTy, $scaleX, $scaleY),
                'ellipse' => self::renderEllipse($child, $page, $elTx, $elTy, $scaleX, $scaleY),
                'polygon' => self::renderPolygon($child, $page, $elTx, $elTy, true, $scaleX),
                'polyline' => self::renderPolygon($child, $page, $elTx, $elTy, false, $scaleX),
                'path' => self::renderPath($child, $page, $elTx, $elTy, $scaleX),
                'text' => self::renderText($child, $page, $elTx, $elTy, $scaleX),
                'g' => self::walkElement($child, $page, $elTx, $elTy, $scaleX, $scaleY),
                'defs' => null, // <defs> children referenced by <use>, not rendered directly.
                'style' => null, // <style> parsed separately, not rendered.
                default => null,
            };
        }
    }

    /**
     * Apply transform matrix to element-level coords; coords
     * then pass through original tx/ty (PDF mapping).
     *
     * @param  array{0: float, 1: float, 2: float, 3: float, 4: float, 5: float}  $matrix  [a, b, c, d, e, f]
     */
    private static function walkChildTransformed(
        \SimpleXMLElement $el,
        Page $page,
        callable $origTx,
        callable $origTy,
        array $matrix,
        float $scaleX,
        float $scaleY,
    ): void {
        [$a, $b, $c, $d, $e, $f] = $matrix;
        // Composed transform: SVG coord (x, y) → transformed (a*x+c*y+e, b*x+d*y+f) → PDF.
        // Single-arg signatures cannot represent cross-coupling, so we
        // construct point-transforming closures for each method.
        // Simplest: wrap tx/ty to accept transformed inputs precomputed
        // by callers. That requires callers compute (a*x + c*y + e, b*x + d*y + f)
        // before calling tx/ty.

        // Effective scale for stroke-width = sqrt(|a*d - b*c|).
        $effScale = $scaleX * sqrt(max(abs($a * $d - $b * $c), 1e-9));

        $newTx = static function (float $x) use ($a, $c, $e, $origTx): float {
            // y component injected later through cross-coupled wrappers.
            // Fallback if used standalone: y = 0.
            return $origTx($a * $x + $c * 0 + $e);
        };
        $newTy = static function (float $y) use ($b, $d, $f, $origTy): float {
            return $origTy($b * 0 + $d * $y + $f);
        };

        // Properly transform requires cross-axis coupling. Use combined
        // wrapper that accepts both x and y as a pair: emit points directly.
        $px = static function (float $x, float $y) use ($a, $c, $e, $origTx): float {
            return $origTx($a * $x + $c * $y + $e);
        };
        $py = static function (float $x, float $y) use ($b, $d, $f, $origTy): float {
            return $origTy($b * $x + $d * $y + $f);
        };

        $tag = $el->getName();
        match ($tag) {
            'rect' => self::renderRectXY($el, $page, $px, $py, $effScale, $scaleY),
            'line' => self::renderLineXY($el, $page, $px, $py, $effScale),
            'circle' => self::renderEllipseXY($el, $page, $px, $py, $effScale, true),
            'ellipse' => self::renderEllipseXY($el, $page, $px, $py, $effScale, false),
            'polygon' => self::renderPolygonXY($el, $page, $px, $py, true, $effScale),
            'polyline' => self::renderPolygonXY($el, $page, $px, $py, false, $effScale),
            'path' => self::renderPathXY($el, $page, $px, $py, $effScale),
            'text' => self::renderTextXY($el, $page, $px, $py, $effScale),
            'g' => self::walkElementXY($el, $page, $px, $py, $effScale, $scaleY),
            default => null,
        };
    }

    /**
     * Walk children with (px, py) cross-coupled point transformers.
     *
     * @param  callable(float, float): float  $px
     * @param  callable(float, float): float  $py
     */
    private static function walkElementXY(\SimpleXMLElement $el, Page $page, callable $px, callable $py, float $scaleX, float $scaleY): void
    {
        foreach ($el->children() as $child) {
            $tag = $child->getName();
            match ($tag) {
                'rect' => self::renderRectXY($child, $page, $px, $py, $scaleX, $scaleY),
                'line' => self::renderLineXY($child, $page, $px, $py, $scaleX),
                'circle' => self::renderEllipseXY($child, $page, $px, $py, $scaleX, true),
                'ellipse' => self::renderEllipseXY($child, $page, $px, $py, $scaleX, false),
                'polygon' => self::renderPolygonXY($child, $page, $px, $py, true, $scaleX),
                'polyline' => self::renderPolygonXY($child, $page, $px, $py, false, $scaleX),
                'path' => self::renderPathXY($child, $page, $px, $py, $scaleX),
                'text' => self::renderTextXY($child, $page, $px, $py, $scaleX),
                'g' => self::walkElementXY($child, $page, $px, $py, $scaleX, $scaleY),
                default => null,
            };
        }
    }

    /**
     * Parse SVG transform attribute → 2×3 matrix [a, b, c, d, e, f].
     * Multiple transforms composed left-to-right per spec.
     *
     * Supported: translate(tx, ty?) | scale(sx, sy?) | rotate(deg, cx?, cy?) | matrix(a, b, c, d, e, f).
     *
     * @return array{0: float, 1: float, 2: float, 3: float, 4: float, 5: float}|null
     */
    private static function parseTransform(string $attr): ?array
    {
        // Identity matrix.
        $M = [1.0, 0.0, 0.0, 1.0, 0.0, 0.0];

        if (! preg_match_all('@(\w+)\s*\(([^)]*)\)@', $attr, $matches, PREG_SET_ORDER)) {
            return null;
        }
        foreach ($matches as $m) {
            $op = strtolower($m[1]);
            $argsRaw = preg_split('@[\s,]+@', trim($m[2])) ?: [];
            $args = array_map('floatval', array_values(array_filter($argsRaw, fn ($s) => $s !== '')));
            $T = self::transformMatrix($op, $args);
            if ($T === null) {
                continue;
            }
            $M = self::multiplyMatrix($M, $T);
        }

        return $M;
    }

    /**
     * @param  list<float>  $args
     * @return array{0: float, 1: float, 2: float, 3: float, 4: float, 5: float}|null
     */
    private static function transformMatrix(string $op, array $args): ?array
    {
        return match ($op) {
            'translate' => [1, 0, 0, 1, $args[0] ?? 0, $args[1] ?? 0],
            'scale' => [$args[0] ?? 1, 0, 0, $args[1] ?? ($args[0] ?? 1), 0, 0],
            'rotate' => self::rotateMatrix(
                deg2rad($args[0] ?? 0),
                $args[1] ?? 0,
                $args[2] ?? 0,
            ),
            'matrix' => count($args) >= 6
                ? [$args[0], $args[1], $args[2], $args[3], $args[4], $args[5]]
                : null,
            default => null,
        };
    }

    /**
     * Rotation about (cx, cy) — Translate(cx, cy) · Rotate(θ) · Translate(-cx, -cy).
     *
     * @return array{0: float, 1: float, 2: float, 3: float, 4: float, 5: float}
     */
    private static function rotateMatrix(float $theta, float $cx, float $cy): array
    {
        $cos = cos($theta);
        $sin = sin($theta);
        if ($cx === 0.0 && $cy === 0.0) {
            return [$cos, $sin, -$sin, $cos, 0.0, 0.0];
        }
        // Composed: T(cx,cy) · R · T(-cx,-cy).
        $tx = $cx - $cos * $cx + $sin * $cy;
        $ty = $cy - $sin * $cx - $cos * $cy;

        return [$cos, $sin, -$sin, $cos, $tx, $ty];
    }

    /**
     * Multiply 2×3 matrices A · B.
     *
     * @param  array{0: float, 1: float, 2: float, 3: float, 4: float, 5: float}  $A
     * @param  array{0: float, 1: float, 2: float, 3: float, 4: float, 5: float}  $B
     * @return array{0: float, 1: float, 2: float, 3: float, 4: float, 5: float}
     */
    private static function multiplyMatrix(array $A, array $B): array
    {
        return [
            $A[0] * $B[0] + $A[2] * $B[1],
            $A[1] * $B[0] + $A[3] * $B[1],
            $A[0] * $B[2] + $A[2] * $B[3],
            $A[1] * $B[2] + $A[3] * $B[3],
            $A[0] * $B[4] + $A[2] * $B[5] + $A[4],
            $A[1] * $B[4] + $A[3] * $B[5] + $A[5],
        ];
    }

    // ── Transformed render methods (XY variants accept point transformers) ───

    /**
     * @param  callable(float, float): float  $px
     * @param  callable(float, float): float  $py
     */
    private static function renderRectXY(\SimpleXMLElement $el, Page $page, callable $px, callable $py, float $scaleX, float $scaleY): void
    {
        // Transformed rectangle → polygon (4 corners preserve rotation).
        $x = (float) ($el['x'] ?? 0);
        $y = (float) ($el['y'] ?? 0);
        $w = (float) ($el['width'] ?? 0);
        $h = (float) ($el['height'] ?? 0);
        [$fr, $fg, $fb, $hasFill] = self::parseColor(isset($el['fill']) ? (string) $el['fill'] : '#000');
        [$sr, $sg, $sb, $hasStroke] = self::parseColor(isset($el['stroke']) ? (string) $el['stroke'] : null);
        $sw = (float) ($el['stroke-width'] ?? 1);

        $corners = [
            [$px($x, $y), $py($x, $y)],
            [$px($x + $w, $y), $py($x + $w, $y)],
            [$px($x + $w, $y + $h), $py($x + $w, $y + $h)],
            [$px($x, $y + $h), $py($x, $y + $h)],
        ];
        if ($hasFill) {
            $page->fillPolygon($corners, $fr, $fg, $fb);
        }
        if ($hasStroke) {
            $line = $corners;
            $line[] = $corners[0];
            $page->strokePolyline($line, $sw * $scaleX, $sr, $sg, $sb);
        }
    }

    /**
     * @param  callable(float, float): float  $px
     * @param  callable(float, float): float  $py
     */
    private static function renderLineXY(\SimpleXMLElement $el, Page $page, callable $px, callable $py, float $scaleX): void
    {
        $x1 = (float) ($el['x1'] ?? 0);
        $y1 = (float) ($el['y1'] ?? 0);
        $x2 = (float) ($el['x2'] ?? 0);
        $y2 = (float) ($el['y2'] ?? 0);
        [$sr, $sg, $sb] = self::parseColor(isset($el['stroke']) ? (string) $el['stroke'] : '#000');
        $sw = (float) ($el['stroke-width'] ?? 1);
        $page->strokeLine($px($x1, $y1), $py($x1, $y1), $px($x2, $y2), $py($x2, $y2), $sw * $scaleX, $sr, $sg, $sb);
    }

    /**
     * @param  callable(float, float): float  $px
     * @param  callable(float, float): float  $py
     */
    private static function renderEllipseXY(\SimpleXMLElement $el, Page $page, callable $px, callable $py, float $scaleX, bool $isCircle): void
    {
        $cx = (float) ($el['cx'] ?? 0);
        $cy = (float) ($el['cy'] ?? 0);
        if ($isCircle) {
            $rx = $ry = (float) ($el['r'] ?? 0);
        } else {
            $rx = (float) ($el['rx'] ?? 0);
            $ry = (float) ($el['ry'] ?? 0);
        }
        [$fr, $fg, $fb, $hasFill] = self::parseColor(isset($el['fill']) ? (string) $el['fill'] : '#000');
        [$sr, $sg, $sb, $hasStroke] = self::parseColor(isset($el['stroke']) ? (string) $el['stroke'] : null);
        $sw = (float) ($el['stroke-width'] ?? 1);

        $segments = 36;
        $points = [];
        for ($i = 0; $i < $segments; $i++) {
            $angle = 2 * M_PI * $i / $segments;
            $sx = $cx + cos($angle) * $rx;
            $sy = $cy + sin($angle) * $ry;
            $points[] = [$px($sx, $sy), $py($sx, $sy)];
        }
        if ($hasFill) {
            $page->fillPolygon($points, $fr, $fg, $fb);
        }
        if ($hasStroke) {
            $closed = $points;
            $closed[] = $points[0];
            $page->strokePolyline($closed, $sw * $scaleX, $sr, $sg, $sb);
        }
    }

    /**
     * @param  callable(float, float): float  $px
     * @param  callable(float, float): float  $py
     */
    private static function renderPolygonXY(\SimpleXMLElement $el, Page $page, callable $px, callable $py, bool $closed, float $scaleX): void
    {
        $pointsAttr = (string) ($el['points'] ?? '');
        $tokens = preg_split('@[\s,]+@', trim($pointsAttr));
        if ($tokens === false) {
            return;
        }
        $tokens = array_values(array_filter($tokens, fn ($t) => $t !== ''));
        $pts = [];
        for ($i = 0; $i + 1 < count($tokens); $i += 2) {
            $sx = (float) $tokens[$i];
            $sy = (float) $tokens[$i + 1];
            $pts[] = [$px($sx, $sy), $py($sx, $sy)];
        }
        if ($pts === []) {
            return;
        }
        [$fr, $fg, $fb, $hasFill] = self::parseColor(isset($el['fill']) ? (string) $el['fill'] : ($closed ? '#000' : null));
        [$sr, $sg, $sb, $hasStroke] = self::parseColor(isset($el['stroke']) ? (string) $el['stroke'] : null);
        $sw = (float) ($el['stroke-width'] ?? 1);

        if ($closed && $hasFill) {
            $page->fillPolygon($pts, $fr, $fg, $fb);
        }
        if ($hasStroke) {
            $line = $pts;
            if ($closed) {
                $line[] = $pts[0];
            }
            $page->strokePolyline($line, $sw * $scaleX, $sr, $sg, $sb);
        }
    }

    /**
     * Transformed path. Parses path with the same rules as renderPath,
     * but coords are transformed via $px/$py closures.
     *
     * @param  callable(float, float): float  $px
     * @param  callable(float, float): float  $py
     */
    private static function renderPathXY(\SimpleXMLElement $el, Page $page, callable $px, callable $py, float $scaleX): void
    {
        $d = (string) ($el['d'] ?? '');
        if ($d === '') {
            return;
        }
        [$fr, $fg, $fb, $hasFill] = self::parseColor(isset($el['fill']) ? (string) $el['fill'] : '#000');
        [$sr, $sg, $sb, $hasStroke] = self::parseColor(isset($el['stroke']) ? (string) $el['stroke'] : null);
        $sw = (float) ($el['stroke-width'] ?? 1);

        // Wrap point transformers as (svg-x → pdf-x) / (svg-y → pdf-y)
        // closures that we'd normally pass to parsePathD. parsePathD expects
        // single-arg tx/ty, so we pass through identity closures and
        // post-transform each emitted command:
        $idTx = static fn (float $x): float => $x;
        $idTy = static fn (float $y): float => $y;
        $rawCommands = self::parsePathD($d, $idTx, $idTy);

        // Transform raw SVG-space commands to PDF coords.
        $commands = [];
        foreach ($rawCommands as $cmd) {
            if ($cmd === 'Z') {
                $commands[] = 'Z';

                continue;
            }
            $type = $cmd[0];
            if ($type === 'M' || $type === 'L') {
                $commands[] = [$type, $px($cmd[1], $cmd[2]), $py($cmd[1], $cmd[2])];
            } elseif ($type === 'C') {
                $commands[] = [
                    'C',
                    $px($cmd[1], $cmd[2]), $py($cmd[1], $cmd[2]),
                    $px($cmd[3], $cmd[4]), $py($cmd[3], $cmd[4]),
                    $px($cmd[5], $cmd[6]), $py($cmd[5], $cmd[6]),
                ];
            }
        }

        if ($hasFill && $hasStroke) {
            $mode = 'fillstroke';
        } elseif ($hasFill) {
            $mode = 'fill';
        } elseif ($hasStroke) {
            $mode = 'stroke';
        } else {
            return;
        }
        $page->emitPath(
            $commands,
            $mode,
            $hasFill ? ['r' => $fr, 'g' => $fg, 'b' => $fb] : null,
            $hasStroke ? ['r' => $sr, 'g' => $sg, 'b' => $sb] : null,
            $sw * $scaleX,
        );
    }

    /**
     * @param  callable(float, float): float  $px
     * @param  callable(float, float): float  $py
     */
    private static function renderTextXY(\SimpleXMLElement $el, Page $page, callable $px, callable $py, float $scaleX): void
    {
        $x = (float) ($el['x'] ?? 0);
        $y = (float) ($el['y'] ?? 0);
        $fontSize = (float) ($el['font-size'] ?? 16);
        [$fr, $fg, $fb, $hasFill] = self::parseColor(isset($el['fill']) ? (string) $el['fill'] : '#000');
        if (! $hasFill) {
            return;
        }
        $text = trim((string) $el);
        if ($text === '') {
            return;
        }
        $page->showText(
            $text, $px($x, $y), $py($x, $y),
            \Dskripchenko\PhpPdf\Pdf\StandardFont::Helvetica,
            $fontSize * $scaleX,
            $fr, $fg, $fb,
        );
    }

    /**
     * SVG <text>. Basic support: x, y, font-size, fill.
     *
     * Limitations:
     *  - Uses StandardFont::Helvetica (no font-family resolution).
     *  - text-anchor: middle/end ignored (default = start).
     *  - tspan / nested elements skipped.
     *
     * @param  callable(float): float  $tx
     * @param  callable(float): float  $ty
     */
    private static function renderText(\SimpleXMLElement $el, Page $page, callable $tx, callable $ty, float $scaleX): void
    {
        $x = (float) ($el['x'] ?? 0);
        $y = (float) ($el['y'] ?? 0);
        $fontSize = (float) ($el['font-size'] ?? 16);
        [$fr, $fg, $fb, $hasFill] = self::parseColor(isset($el['fill']) ? (string) $el['fill'] : '#000');
        if (! $hasFill) {
            return;
        }
        // SVG text positioned at baseline; in PDF showText X-Y are used
        // the same way (Y = baseline).
        $text = trim((string) $el);
        if ($text === '') {
            return;
        }
        $page->showText(
            $text, $tx($x), $ty($y),
            \Dskripchenko\PhpPdf\Pdf\StandardFont::Helvetica,
            $fontSize * $scaleX, // Scale font size with x-scale.
            $fr, $fg, $fb,
        );
    }

    /**
     * @param  callable(float): float  $tx
     * @param  callable(float): float  $ty
     */
    private static function renderRect(\SimpleXMLElement $el, Page $page, callable $tx, callable $ty, float $scaleX, float $scaleY): void
    {
        $x = (float) ($el['x'] ?? 0);
        $y = (float) ($el['y'] ?? 0);
        $w = (float) ($el['width'] ?? 0);
        $h = (float) ($el['height'] ?? 0);

        // Detect fill="url(#id)" → use shading pattern.
        $fillAttr = (string) ($el['fill'] ?? '#000');
        $patternName = null;
        if (preg_match('@^url\(#([^)]+)\)$@', trim($fillAttr), $m)) {
            $gradId = $m[1];
            if (isset(self::$currentGradients[$gradId])) {
                $patternName = self::createPatternFromGradient(
                    $page, self::$currentGradients[$gradId],
                    $tx($x), $ty($y + $h), $w * $scaleX, $h * $scaleY,
                );
            }
        }

        [$fr, $fg, $fb, $hasFill] = self::parseColor($fillAttr);
        [$sr, $sg, $sb, $hasStroke] = self::parseColor(isset($el['stroke']) ? (string) $el['stroke'] : null);
        $sw = (float) ($el['stroke-width'] ?? 1);

        $globalOpacity = self::parseOpacity($el, 'opacity', 1.0);
        $fillOpacity = self::parseOpacity($el, 'fill-opacity', $globalOpacity);
        $strokeOpacity = self::parseOpacity($el, 'stroke-opacity', $globalOpacity);

        $px = $tx($x);
        $pw = $w * $scaleX;
        $ph = $h * $scaleY;
        $py = $ty($y + $h);

        // Rounded corners: rx (ry falls back to rx, as in SVG). Pattern
        // fills stay square — clipping a shading to a rounded path is
        // future work.
        $radius = max((float) ($el['rx'] ?? 0), (float) ($el['ry'] ?? 0)) * min($scaleX, $scaleY);

        $page->withOpacity($fillOpacity, $strokeOpacity, static function () use ($page, $hasFill, $hasStroke, $px, $py, $pw, $ph, $fr, $fg, $fb, $sr, $sg, $sb, $sw, $scaleX, $patternName, $radius): void {
            if ($patternName !== null) {
                $page->fillRectWithPattern($px, $py, $pw, $ph, $patternName);
            } elseif ($hasFill) {
                $radius > 0
                    ? $page->fillRoundedRect($px, $py, $pw, $ph, $radius, $fr, $fg, $fb)
                    : $page->fillRect($px, $py, $pw, $ph, $fr, $fg, $fb);
            }
            if ($hasStroke) {
                $radius > 0
                    ? $page->strokeRoundedRect($px, $py, $pw, $ph, $radius, $sw * $scaleX, $sr, $sg, $sb)
                    : $page->strokeRect($px, $py, $pw, $ph, $sw * $scaleX, $sr, $sg, $sb);
            }
        });
    }

    /**
     * Create + register PDF shading pattern from SVG gradient.
     *
     * @param  array<string, mixed>  $gradient
     */
    private static function createPatternFromGradient(
        Page $page, array $gradient,
        float $rectX, float $rectY, float $rectW, float $rectH,
    ): ?string {
        $stops = $gradient['stops'];
        if (count($stops) < 2) {
            return null;
        }

        // Radial gradient — 6-element coords (cx0, cy0, r0, cx1, cy1, r1).
        if (($gradient['type'] ?? 'linear') === 'radial') {
            $cx = $rectX + $gradient['cx'] * $rectW;
            $cy = $rectY + $rectH - $gradient['cy'] * $rectH;
            $r = $gradient['r'] * min($rectW, $rectH);
            $fx = $rectX + $gradient['fx'] * $rectW;
            $fy = $rectY + $rectH - $gradient['fy'] * $rectH;
            // From focal (r=0) outward to (cx, cy, r).
            $coords = [$fx, $fy, 0.0, $cx, $cy, $r];
            $shadingType = \Dskripchenko\PhpPdf\Pdf\PdfShading::TYPE_RADIAL;
        } else {
            $x1 = $rectX + $gradient['x1'] * $rectW;
            $y1 = $rectY + $rectH - $gradient['y1'] * $rectH;
            $x2 = $rectX + $gradient['x2'] * $rectW;
            $y2 = $rectY + $rectH - $gradient['y2'] * $rectH;
            $coords = [$x1, $y1, $x2, $y2];
            $shadingType = \Dskripchenko\PhpPdf\Pdf\PdfShading::TYPE_AXIAL;
        }

        // 2 stops — single Type 2 function.
        // >2 stops — Type 3 stitching of multiple Type 2 sub-functions.
        if (count($stops) === 2) {
            $function = new \Dskripchenko\PhpPdf\Pdf\PdfFunction(
                c0: $stops[0]['color'],
                c1: $stops[1]['color'],
            );
        } else {
            $subFunctions = [];
            $bounds = [];
            $encode = [];
            for ($i = 0; $i < count($stops) - 1; $i++) {
                $subFunctions[] = new \Dskripchenko\PhpPdf\Pdf\PdfFunction(
                    c0: $stops[$i]['color'],
                    c1: $stops[$i + 1]['color'],
                );
                if ($i < count($stops) - 2) {
                    $bounds[] = $stops[$i + 1]['offset'];
                }
                $encode[] = 0;
                $encode[] = 1;
            }
            $function = new \Dskripchenko\PhpPdf\Pdf\PdfStitchingFunction(
                subFunctions: $subFunctions,
                bounds: $bounds,
                encode: $encode,
            );
        }
        $shading = new \Dskripchenko\PhpPdf\Pdf\PdfShading(
            shadingType: $shadingType,
            coords: $coords,
            function: $function,
        );
        // Forward gradientTransform to pattern matrix.
        $matrix = $gradient['transform'] ?? null;
        $pattern = new \Dskripchenko\PhpPdf\Pdf\PdfPattern($shading, $matrix);

        return $page->registerShadingPattern($pattern);
    }

    /**
     * @param  callable(float): float  $tx
     * @param  callable(float): float  $ty
     */
    private static function renderLine(\SimpleXMLElement $el, Page $page, callable $tx, callable $ty, float $scaleX): void
    {
        $x1 = (float) ($el['x1'] ?? 0);
        $y1 = (float) ($el['y1'] ?? 0);
        $x2 = (float) ($el['x2'] ?? 0);
        $y2 = (float) ($el['y2'] ?? 0);
        [$sr, $sg, $sb] = self::parseColor(isset($el['stroke']) ? (string) $el['stroke'] : '#000');
        $sw = (float) ($el['stroke-width'] ?? 1);
        $page->strokeLine($tx($x1), $ty($y1), $tx($x2), $ty($y2), $sw * $scaleX, $sr, $sg, $sb);
    }

    /**
     * @param  callable(float): float  $tx
     * @param  callable(float): float  $ty
     */
    private static function renderCircle(\SimpleXMLElement $el, Page $page, callable $tx, callable $ty, float $scaleX, float $scaleY): void
    {
        $cx = (float) ($el['cx'] ?? 0);
        $cy = (float) ($el['cy'] ?? 0);
        $r = (float) ($el['r'] ?? 0);
        self::renderEllipseAt($cx, $cy, $r, $r, $el, $page, $tx, $ty, $scaleX, $scaleY);
    }

    /**
     * @param  callable(float): float  $tx
     * @param  callable(float): float  $ty
     */
    private static function renderEllipse(\SimpleXMLElement $el, Page $page, callable $tx, callable $ty, float $scaleX, float $scaleY): void
    {
        $cx = (float) ($el['cx'] ?? 0);
        $cy = (float) ($el['cy'] ?? 0);
        $rx = (float) ($el['rx'] ?? 0);
        $ry = (float) ($el['ry'] ?? 0);
        self::renderEllipseAt($cx, $cy, $rx, $ry, $el, $page, $tx, $ty, $scaleX, $scaleY);
    }

    /**
     * Ellipse → polygon approximation (36 segments).
     *
     * @param  callable(float): float  $tx
     * @param  callable(float): float  $ty
     */
    private static function renderEllipseAt(float $cx, float $cy, float $rx, float $ry, \SimpleXMLElement $el, Page $page, callable $tx, callable $ty, float $scaleX, float $scaleY): void
    {
        $segments = 36;
        $points = [];
        for ($i = 0; $i < $segments; $i++) {
            $angle = 2 * M_PI * $i / $segments;
            $points[] = [$tx($cx + cos($angle) * $rx), $ty($cy + sin($angle) * $ry)];
        }
        [$fr, $fg, $fb, $hasFill] = self::parseColor(isset($el['fill']) ? (string) $el['fill'] : '#000');
        [$sr, $sg, $sb, $hasStroke] = self::parseColor(isset($el['stroke']) ? (string) $el['stroke'] : null);
        $sw = (float) ($el['stroke-width'] ?? 1);

        // Opacity.
        $globalOpacity = self::parseOpacity($el, 'opacity', 1.0);
        $fillOpacity = self::parseOpacity($el, 'fill-opacity', $globalOpacity);
        $strokeOpacity = self::parseOpacity($el, 'stroke-opacity', $globalOpacity);

        $page->withOpacity($fillOpacity, $strokeOpacity, static function () use ($page, $hasFill, $hasStroke, $points, $fr, $fg, $fb, $sr, $sg, $sb, $sw, $scaleX): void {
            if ($hasFill) {
                $page->fillPolygon($points, $fr, $fg, $fb);
            }
            if ($hasStroke) {
                $closed = $points;
                $closed[] = $points[0];
                $page->strokePolyline($closed, $sw * $scaleX, $sr, $sg, $sb);
            }
        });
    }

    /**
     * @param  callable(float): float  $tx
     * @param  callable(float): float  $ty
     */
    private static function renderPolygon(\SimpleXMLElement $el, Page $page, callable $tx, callable $ty, bool $closed, float $scaleX): void
    {
        $pointsAttr = (string) ($el['points'] ?? '');
        $points = self::parsePoints($pointsAttr, $tx, $ty);
        if ($points === []) {
            return;
        }
        [$fr, $fg, $fb, $hasFill] = self::parseColor(isset($el['fill']) ? (string) $el['fill'] : ($closed ? '#000' : null));
        [$sr, $sg, $sb, $hasStroke] = self::parseColor(isset($el['stroke']) ? (string) $el['stroke'] : null);
        $sw = (float) ($el['stroke-width'] ?? 1);

        $globalOpacity = self::parseOpacity($el, 'opacity', 1.0);
        $fillOpacity = self::parseOpacity($el, 'fill-opacity', $globalOpacity);
        $strokeOpacity = self::parseOpacity($el, 'stroke-opacity', $globalOpacity);

        $page->withOpacity($fillOpacity, $strokeOpacity, static function () use ($page, $closed, $hasFill, $hasStroke, $points, $fr, $fg, $fb, $sr, $sg, $sb, $sw, $scaleX): void {
            if ($closed && $hasFill) {
                $page->fillPolygon($points, $fr, $fg, $fb);
            }
            if ($hasStroke) {
                $line = $points;
                if ($closed) {
                    $line[] = $points[0];
                }
                $page->strokePolyline($line, $sw * $scaleX, $sr, $sg, $sb);
            }
        });
    }

    /**
     * @param  callable(float): float  $tx
     * @param  callable(float): float  $ty
     * @return list<array{0: float, 1: float}>
     */
    private static function parsePoints(string $raw, callable $tx, callable $ty): array
    {
        $tokens = preg_split('@[\s,]+@', trim($raw));
        if ($tokens === false) {
            return [];
        }
        $tokens = array_values(array_filter($tokens, fn ($t) => $t !== ''));
        $out = [];
        for ($i = 0; $i + 1 < count($tokens); $i += 2) {
            $out[] = [$tx((float) $tokens[$i]), $ty((float) $tokens[$i + 1])];
        }

        return $out;
    }

    /**
     * SVG path parser supporting M/L/H/V/C/S/Q/T/Z commands
     * (uppercase = absolute, lowercase = relative).
     *
     * Arc (A/a) — converted to cubic Beziers (see arcToCubics).
     *
     * @param  callable(float): float  $tx
     * @param  callable(float): float  $ty
     */
    private static function renderPath(\SimpleXMLElement $el, Page $page, callable $tx, callable $ty, float $scaleX): void
    {
        $d = (string) ($el['d'] ?? '');
        if ($d === '') {
            return;
        }
        [$fr, $fg, $fb, $hasFill] = self::parseColor(isset($el['fill']) ? (string) $el['fill'] : '#000');
        [$sr, $sg, $sb, $hasStroke] = self::parseColor(isset($el['stroke']) ? (string) $el['stroke'] : null);
        $sw = (float) ($el['stroke-width'] ?? 1);

        $commands = self::parsePathD($d, $tx, $ty);
        if ($commands === []) {
            return;
        }

        // Determine fill/stroke mode.
        if ($hasFill && $hasStroke) {
            $mode = 'fillstroke';
        } elseif ($hasFill) {
            $mode = 'fill';
        } elseif ($hasStroke) {
            $mode = 'stroke';
        } else {
            return; // Nothing to draw.
        }

        $page->emitPath(
            $commands,
            $mode,
            $hasFill ? ['r' => $fr, 'g' => $fg, 'b' => $fb] : null,
            $hasStroke ? ['r' => $sr, 'g' => $sg, 'b' => $sb] : null,
            $sw * $scaleX,
        );
    }

    /**
     * Parse SVG path "d" attribute → list of PDF-compatible path commands.
     * Coordinates already transformed via $tx/$ty.
     *
     * Quadratic Bezier (Q/T) auto-converted to cubic Bezier (PDF has no
     * quadratic operator).
     *
     * @param  callable(float): float  $tx
     * @param  callable(float): float  $ty
     * @return list<array{0: string, 1: float, 2: float, 3?: float, 4?: float, 5?: float, 6?: float}|string>
     */
    private static function parsePathD(string $d, callable $tx, callable $ty): array
    {
        // Tokenize: split on each command letter (preserve command letters as separators).
        $regex = '@([MmLlHhVvCcSsQqTtAaZz])([^MmLlHhVvCcSsQqTtAaZz]*)@';
        if (! preg_match_all($regex, $d, $matches, PREG_SET_ORDER)) {
            return [];
        }
        $commands = [];
        $cx = 0;
        $cy = 0; // current point (SVG units, NOT transformed)
        $startX = 0;
        $startY = 0; // path start for Z
        $lastCubicCtrlX = null;
        $lastCubicCtrlY = null;
        $lastQuadCtrlX = null;
        $lastQuadCtrlY = null;

        foreach ($matches as $m) {
            $op = $m[1];
            $args = trim($m[2]);
            $nums = $args === '' ? [] : preg_split('@[\s,]+|(?<=[0-9.])-@', $args);
            if ($nums === false) {
                $nums = [];
            }
            $nums = array_values(array_map('floatval', array_filter($nums, fn ($n) => $n !== '')));
            $isRel = ctype_lower($op);
            $opUp = strtoupper($op);

            switch ($opUp) {
                case 'M':
                    if (count($nums) < 2) {
                        break;
                    }
                    $x = $isRel ? $cx + $nums[0] : $nums[0];
                    $y = $isRel ? $cy + $nums[1] : $nums[1];
                    $commands[] = ['M', $tx($x), $ty($y)];
                    $cx = $x; $cy = $y;
                    $startX = $x; $startY = $y;
                    // Subsequent pairs treated as L.
                    for ($i = 2; $i + 1 < count($nums); $i += 2) {
                        $x = $isRel ? $cx + $nums[$i] : $nums[$i];
                        $y = $isRel ? $cy + $nums[$i + 1] : $nums[$i + 1];
                        $commands[] = ['L', $tx($x), $ty($y)];
                        $cx = $x; $cy = $y;
                    }
                    $lastCubicCtrlX = $lastCubicCtrlY = null;
                    $lastQuadCtrlX = $lastQuadCtrlY = null;
                    break;

                case 'L':
                    for ($i = 0; $i + 1 < count($nums); $i += 2) {
                        $x = $isRel ? $cx + $nums[$i] : $nums[$i];
                        $y = $isRel ? $cy + $nums[$i + 1] : $nums[$i + 1];
                        $commands[] = ['L', $tx($x), $ty($y)];
                        $cx = $x; $cy = $y;
                    }
                    $lastCubicCtrlX = $lastCubicCtrlY = null;
                    $lastQuadCtrlX = $lastQuadCtrlY = null;
                    break;

                case 'H':
                    foreach ($nums as $n) {
                        $x = $isRel ? $cx + $n : $n;
                        $commands[] = ['L', $tx($x), $ty($cy)];
                        $cx = $x;
                    }
                    $lastCubicCtrlX = $lastCubicCtrlY = null;
                    $lastQuadCtrlX = $lastQuadCtrlY = null;
                    break;

                case 'V':
                    foreach ($nums as $n) {
                        $y = $isRel ? $cy + $n : $n;
                        $commands[] = ['L', $tx($cx), $ty($y)];
                        $cy = $y;
                    }
                    $lastCubicCtrlX = $lastCubicCtrlY = null;
                    $lastQuadCtrlX = $lastQuadCtrlY = null;
                    break;

                case 'C':
                    for ($i = 0; $i + 5 < count($nums); $i += 6) {
                        $x1 = $isRel ? $cx + $nums[$i] : $nums[$i];
                        $y1 = $isRel ? $cy + $nums[$i + 1] : $nums[$i + 1];
                        $x2 = $isRel ? $cx + $nums[$i + 2] : $nums[$i + 2];
                        $y2 = $isRel ? $cy + $nums[$i + 3] : $nums[$i + 3];
                        $x3 = $isRel ? $cx + $nums[$i + 4] : $nums[$i + 4];
                        $y3 = $isRel ? $cy + $nums[$i + 5] : $nums[$i + 5];
                        $commands[] = ['C', $tx($x1), $ty($y1), $tx($x2), $ty($y2), $tx($x3), $ty($y3)];
                        $cx = $x3; $cy = $y3;
                        $lastCubicCtrlX = $x2;
                        $lastCubicCtrlY = $y2;
                    }
                    $lastQuadCtrlX = $lastQuadCtrlY = null;
                    break;

                case 'S':
                    for ($i = 0; $i + 3 < count($nums); $i += 4) {
                        // First control = reflection prev cubic ctrl OR current point.
                        if ($lastCubicCtrlX !== null) {
                            $x1 = 2 * $cx - $lastCubicCtrlX;
                            $y1 = 2 * $cy - $lastCubicCtrlY;
                        } else {
                            $x1 = $cx; $y1 = $cy;
                        }
                        $x2 = $isRel ? $cx + $nums[$i] : $nums[$i];
                        $y2 = $isRel ? $cy + $nums[$i + 1] : $nums[$i + 1];
                        $x3 = $isRel ? $cx + $nums[$i + 2] : $nums[$i + 2];
                        $y3 = $isRel ? $cy + $nums[$i + 3] : $nums[$i + 3];
                        $commands[] = ['C', $tx($x1), $ty($y1), $tx($x2), $ty($y2), $tx($x3), $ty($y3)];
                        $cx = $x3; $cy = $y3;
                        $lastCubicCtrlX = $x2;
                        $lastCubicCtrlY = $y2;
                    }
                    $lastQuadCtrlX = $lastQuadCtrlY = null;
                    break;

                case 'Q':
                    for ($i = 0; $i + 3 < count($nums); $i += 4) {
                        $qx = $isRel ? $cx + $nums[$i] : $nums[$i];
                        $qy = $isRel ? $cy + $nums[$i + 1] : $nums[$i + 1];
                        $ex = $isRel ? $cx + $nums[$i + 2] : $nums[$i + 2];
                        $ey = $isRel ? $cy + $nums[$i + 3] : $nums[$i + 3];
                        // Quadratic to cubic: P1 = start + 2/3 * (Q - start); P2 = end + 2/3 * (Q - end).
                        $c1x = $cx + 2 / 3 * ($qx - $cx);
                        $c1y = $cy + 2 / 3 * ($qy - $cy);
                        $c2x = $ex + 2 / 3 * ($qx - $ex);
                        $c2y = $ey + 2 / 3 * ($qy - $ey);
                        $commands[] = ['C', $tx($c1x), $ty($c1y), $tx($c2x), $ty($c2y), $tx($ex), $ty($ey)];
                        $cx = $ex; $cy = $ey;
                        $lastQuadCtrlX = $qx;
                        $lastQuadCtrlY = $qy;
                    }
                    $lastCubicCtrlX = $lastCubicCtrlY = null;
                    break;

                case 'T':
                    for ($i = 0; $i + 1 < count($nums); $i += 2) {
                        if ($lastQuadCtrlX !== null) {
                            $qx = 2 * $cx - $lastQuadCtrlX;
                            $qy = 2 * $cy - $lastQuadCtrlY;
                        } else {
                            $qx = $cx; $qy = $cy;
                        }
                        $ex = $isRel ? $cx + $nums[$i] : $nums[$i];
                        $ey = $isRel ? $cy + $nums[$i + 1] : $nums[$i + 1];
                        $c1x = $cx + 2 / 3 * ($qx - $cx);
                        $c1y = $cy + 2 / 3 * ($qy - $cy);
                        $c2x = $ex + 2 / 3 * ($qx - $ex);
                        $c2y = $ey + 2 / 3 * ($qy - $ey);
                        $commands[] = ['C', $tx($c1x), $ty($c1y), $tx($c2x), $ty($c2y), $tx($ex), $ty($ey)];
                        $cx = $ex; $cy = $ey;
                        $lastQuadCtrlX = $qx;
                        $lastQuadCtrlY = $qy;
                    }
                    $lastCubicCtrlX = $lastCubicCtrlY = null;
                    break;

                case 'Z':
                    $commands[] = 'Z';
                    $cx = $startX;
                    $cy = $startY;
                    $lastCubicCtrlX = $lastCubicCtrlY = null;
                    $lastQuadCtrlX = $lastQuadCtrlY = null;
                    break;

                case 'A':
                    // Elliptical arc — convert to cubic Beziers.
                    // 7 args per arc: rx, ry, x-axis-rot, large-arc-flag, sweep-flag, x, y.
                    for ($i = 0; $i + 6 < count($nums); $i += 7) {
                        $arcRx = $nums[$i];
                        $arcRy = $nums[$i + 1];
                        $xRot = $nums[$i + 2];
                        $largeArc = (bool) $nums[$i + 3];
                        $sweep = (bool) $nums[$i + 4];
                        $ex = $isRel ? $cx + $nums[$i + 5] : $nums[$i + 5];
                        $ey = $isRel ? $cy + $nums[$i + 6] : $nums[$i + 6];
                        $arcCubics = self::arcToCubics($cx, $cy, $arcRx, $arcRy, $xRot, $largeArc, $sweep, $ex, $ey);
                        foreach ($arcCubics as $cu) {
                            $commands[] = [
                                'C',
                                $tx($cu[0]), $ty($cu[1]),
                                $tx($cu[2]), $ty($cu[3]),
                                $tx($cu[4]), $ty($cu[5]),
                            ];
                        }
                        $cx = $ex; $cy = $ey;
                    }
                    $lastCubicCtrlX = $lastCubicCtrlY = null;
                    $lastQuadCtrlX = $lastQuadCtrlY = null;
                    break;
            }
        }

        return $commands;
    }

    /**
     * Convert elliptical arc to a sequence of cubic Beziers.
     *
     * Standard SVG endpoint→center parameterization (W3C Implementation
     * Notes §B.2.4) + 90° max-arc subdivision.
     *
     * @return list<array{0: float, 1: float, 2: float, 3: float, 4: float, 5: float}>
     *   List of cubic segments [c1x, c1y, c2x, c2y, ex, ey].
     */
    private static function arcToCubics(
        float $x1, float $y1, float $rx, float $ry, float $xAxisRot,
        bool $fA, bool $fS, float $x2, float $y2,
    ): array {
        if ($rx == 0.0 || $ry == 0.0 || ($x1 == $x2 && $y1 == $y2)) {
            return [];
        }
        $rx = abs($rx);
        $ry = abs($ry);
        $phi = deg2rad($xAxisRot);
        $cosPhi = cos($phi);
        $sinPhi = sin($phi);

        // Step 1: midpoint rotation.
        $dx = ($x1 - $x2) / 2;
        $dy = ($y1 - $y2) / 2;
        $x1p = $cosPhi * $dx + $sinPhi * $dy;
        $y1p = -$sinPhi * $dx + $cosPhi * $dy;

        // Step 2: ensure radii are sufficient.
        $lambda = ($x1p * $x1p) / ($rx * $rx) + ($y1p * $y1p) / ($ry * $ry);
        if ($lambda > 1) {
            $scale = sqrt($lambda);
            $rx *= $scale;
            $ry *= $scale;
        }

        // Step 3: compute center in rotated frame.
        $sign = $fA === $fS ? -1.0 : 1.0;
        $sq = max(
            0.0,
            ($rx * $rx * $ry * $ry - $rx * $rx * $y1p * $y1p - $ry * $ry * $x1p * $x1p)
            / ($rx * $rx * $y1p * $y1p + $ry * $ry * $x1p * $x1p),
        );
        $factor = $sign * sqrt($sq);
        $cxp = $factor * ($rx * $y1p / $ry);
        $cyp = $factor * -($ry * $x1p / $rx);

        // Step 4: transform back to user space.
        $cx = $cosPhi * $cxp - $sinPhi * $cyp + ($x1 + $x2) / 2;
        $cy = $sinPhi * $cxp + $cosPhi * $cyp + ($y1 + $y2) / 2;

        // Step 5: compute angles.
        $startVec = [($x1p - $cxp) / $rx, ($y1p - $cyp) / $ry];
        $endVec = [(-$x1p - $cxp) / $rx, (-$y1p - $cyp) / $ry];

        $theta1 = self::angleBetween([1.0, 0.0], $startVec);
        $dtheta = self::angleBetween($startVec, $endVec);
        if (! $fS && $dtheta > 0) {
            $dtheta -= 2 * M_PI;
        } elseif ($fS && $dtheta < 0) {
            $dtheta += 2 * M_PI;
        }

        // Step 6: split into segments ≤ 90°.
        $segments = max(1, (int) ceil(abs($dtheta) / (M_PI / 2)));
        $delta = $dtheta / $segments;
        $t = 8.0 / 3.0 * sin($delta / 4) * sin($delta / 4) / sin($delta / 2);

        $cubics = [];
        for ($i = 0; $i < $segments; $i++) {
            $angleStart = $theta1 + $i * $delta;
            $angleEnd = $angleStart + $delta;

            $cosA = cos($angleStart);
            $sinA = sin($angleStart);
            $cosB = cos($angleEnd);
            $sinB = sin($angleEnd);

            // Unit ellipse points.
            $p1 = [$cosA, $sinA];
            $c1 = [$cosA - $t * $sinA, $sinA + $t * $cosA];
            $c2 = [$cosB + $t * $sinB, $sinB - $t * $cosB];
            $p2 = [$cosB, $sinB];

            // Scale and rotate.
            $apply = static function (array $pt) use ($rx, $ry, $cosPhi, $sinPhi, $cx, $cy): array {
                $sx = $pt[0] * $rx;
                $sy = $pt[1] * $ry;

                return [
                    $cosPhi * $sx - $sinPhi * $sy + $cx,
                    $sinPhi * $sx + $cosPhi * $sy + $cy,
                ];
            };
            // p1 not needed — already at current point after prev segment.
            [$c1x, $c1y] = $apply($c1);
            [$c2x, $c2y] = $apply($c2);
            [$p2x, $p2y] = $apply($p2);
            $cubics[] = [$c1x, $c1y, $c2x, $c2y, $p2x, $p2y];
        }

        return $cubics;
    }

    /**
     * Angle between two 2D vectors (signed).
     *
     * @param  array{0: float, 1: float}  $u
     * @param  array{0: float, 1: float}  $v
     */
    private static function angleBetween(array $u, array $v): float
    {
        $dot = $u[0] * $v[0] + $u[1] * $v[1];
        $len = sqrt(($u[0] * $u[0] + $u[1] * $u[1]) * ($v[0] * $v[0] + $v[1] * $v[1]));
        $cosTheta = $len > 0 ? max(-1.0, min(1.0, $dot / $len)) : 1.0;
        $angle = acos($cosTheta);
        $cross = $u[0] * $v[1] - $u[1] * $v[0];

        return $cross < 0 ? -$angle : $angle;
    }
}
