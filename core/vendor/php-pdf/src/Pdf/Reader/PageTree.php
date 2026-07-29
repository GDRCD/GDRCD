<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * Walks the page tree (`/Root → /Pages → /Kids`) in reading order, flattening
 * the four inheritable attributes down to each leaf (ISO 32000-1 §7.7.3.4).
 *
 * Robust to intermediate nodes that omit `/Type`, to reference cycles, and to
 * a missing `/MediaBox` (defaults to US Letter).
 */
final class PageTree
{
    private const DEFAULT_MEDIA_BOX = [0.0, 0.0, 612.0, 792.0];
    private const INHERITABLE = ['Resources', 'MediaBox', 'CropBox', 'Rotate'];

    public function __construct(private readonly ReaderDocument $doc)
    {
    }

    /**
     * @return list<ReaderPage>
     */
    public function pages(): array
    {
        $root = $this->doc->deref($this->doc->catalog()->get('Pages'));
        if (!$root instanceof PdfDictionary) {
            throw new PdfParseException('Page tree root (/Pages) is missing or invalid');
        }

        $pages = [];
        $this->walk($root, -1, [], $pages, []);
        return $pages;
    }

    /**
     * @param array<string,mixed> $inherited
     * @param list<ReaderPage>    $pages
     * @param array<int,true>     $visited object numbers currently on the path
     */
    private function walk(
        PdfDictionary $node,
        int $objectNumber,
        array $inherited,
        array &$pages,
        array $visited,
    ): void {
        // Merge this node's own inheritable attributes over what came down.
        foreach (self::INHERITABLE as $key) {
            if ($node->has($key)) {
                $inherited[$key] = $node->get($key);
            }
        }

        $kids = $this->doc->deref($node->get('Kids'));
        $isInternal = is_array($kids);

        if (!$isInternal) {
            $pages[] = $this->makePage($node, $objectNumber, $inherited);
            return;
        }

        foreach ($kids as $kid) {
            $childNumber = $kid instanceof PdfReference ? $kid->number : -1;
            if ($childNumber >= 0 && isset($visited[$childNumber])) {
                continue; // cycle guard
            }
            $child = $this->doc->deref($kid);
            if (!$child instanceof PdfDictionary) {
                continue;
            }
            $nextVisited = $visited;
            if ($childNumber >= 0) {
                $nextVisited[$childNumber] = true;
            }
            $this->walk($child, $childNumber, $inherited, $pages, $nextVisited);
        }
    }

    /**
     * @param array<string,mixed> $inherited
     */
    private function makePage(PdfDictionary $node, int $objectNumber, array $inherited): ReaderPage
    {
        $mediaBox = $this->rect($inherited['MediaBox'] ?? null) ?? self::DEFAULT_MEDIA_BOX;
        $cropBox = $this->rect($inherited['CropBox'] ?? null) ?? $mediaBox;

        $rotate = $this->doc->deref($inherited['Rotate'] ?? null);
        $rotate = is_int($rotate) ? ((($rotate % 360) + 360) % 360) : 0;

        $resources = $this->doc->deref($inherited['Resources'] ?? null);
        $resources = $resources instanceof PdfDictionary ? $resources : null;

        return new ReaderPage($objectNumber, $node, $mediaBox, $cropBox, $rotate, $resources);
    }

    /**
     * @return array{float,float,float,float}|null
     */
    private function rect(mixed $value): ?array
    {
        $value = $this->doc->deref($value);
        if (!is_array($value) || count($value) < 4) {
            return null;
        }
        $out = [];
        for ($i = 0; $i < 4; $i++) {
            $n = $this->doc->deref($value[$i]);
            if (!is_int($n) && !is_float($n)) {
                return null;
            }
            $out[$i] = (float) $n;
        }
        // Normalize to [llx, lly, urx, ury].
        return [
            min($out[0], $out[2]),
            min($out[1], $out[3]),
            max($out[0], $out[2]),
            max($out[1], $out[3]),
        ];
    }
}
