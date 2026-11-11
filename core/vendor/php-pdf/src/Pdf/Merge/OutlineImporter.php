<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Merge;

use Dskripchenko\PhpPdf\Pdf\Reader\PdfDictionary;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfName;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfParseException;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfReference;
use Dskripchenko\PhpPdf\Pdf\Reader\ReaderDocument;

/**
 * Rebuilds a merged document outline (bookmarks) from the source documents'
 * `/Outlines` trees, concatenated in append order, with each item's
 * destination remapped to the new output pages (ISO 32000-1 §12.3.3).
 *
 * Items whose destination points at a page that was not included are dropped
 * together with their subtree. `/Count` values are recomputed for the new
 * tree; all items are emitted open (positive counts).
 */
final class OutlineImporter
{
    public function __construct(
        private readonly ObjectImporter $importer,
        private readonly DestinationRemapper $remapper,
    ) {
    }

    /**
     * @param list<ReaderDocument>          $docs    distinct sources in append order
     * @param array<int,array<int,int>>     $pageMap spl_object_id → (src page objnum → new page ID)
     * @return int|null new /Outlines object ID, or null when there are no bookmarks
     */
    public function build(array $docs, array $pageMap): ?int
    {
        if (!$this->anyOutlines($docs)) {
            return null;
        }

        $rootId = $this->importer->allocate(null);
        $rootRef = new PdfReference($rootId, 0);

        /** @var list<array{id:int,total:int}> $top */
        $top = [];
        foreach ($docs as $doc) {
            $outlines = $this->outlinesOf($doc);
            if ($outlines === null) {
                continue;
            }
            $this->importer->useSource($doc);
            $map = $pageMap[spl_object_id($doc)] ?? [];
            $top = array_merge($top, $this->buildChain($doc, $outlines->get('First'), $rootRef, $map));
        }

        if ($top === []) {
            // Every item was dangling; emit an empty (but valid) outline root.
            $this->importer->set($rootId, new PdfDictionary(['Type' => new PdfName('Outlines'), 'Count' => 0]));
            return $rootId;
        }

        $this->wireSiblings($top);
        $this->importer->set($rootId, new PdfDictionary([
            'Type' => new PdfName('Outlines'),
            'First' => new PdfReference($top[0]['id'], 0),
            'Last' => new PdfReference($top[count($top) - 1]['id'], 0),
            'Count' => $this->sumTotals($top),
        ]));

        return $rootId;
    }

    /**
     * Build a sibling chain starting at $firstRef.
     *
     * @param array<int,int> $map
     * @return list<array{id:int,total:int}>
     */
    private function buildChain(ReaderDocument $doc, mixed $firstRef, PdfReference $parentRef, array $map): array
    {
        $items = [];
        $cur = $firstRef;
        $guard = 0;
        while ($cur instanceof PdfReference && $guard++ < 100000) {
            $item = $doc->deref($cur);
            if (!$item instanceof PdfDictionary) {
                break;
            }
            $built = $this->buildItem($doc, $item, $parentRef, $map);
            if ($built !== null) {
                $items[] = $built;
            }
            $cur = $item->get('Next');
        }
        $this->wireSiblings($items);
        return $items;
    }

    /**
     * @param array<int,int> $map
     * @return array{id:int,total:int}|null
     */
    private function buildItem(ReaderDocument $doc, PdfDictionary $item, PdfReference $parentRef, array $map): ?array
    {
        $link = $this->remapper->resolveLink($doc, $item, $map);
        if ($link === null) {
            return null; // dangling destination → drop item and its subtree
        }

        $id = $this->importer->allocate(null);
        $selfRef = new PdfReference($id, 0);
        $children = $this->buildChain($doc, $item->get('First'), $selfRef, $map);
        $descendants = $this->sumTotals($children);

        $fields = ['Parent' => $parentRef];
        $title = $item->get('Title');
        if ($title !== null) {
            $fields['Title'] = $this->importer->importValue($title);
        }
        foreach ($link as $k => $v) {
            $fields[$k] = $v;
        }
        if ($children !== []) {
            $fields['First'] = new PdfReference($children[0]['id'], 0);
            $fields['Last'] = new PdfReference($children[count($children) - 1]['id'], 0);
            $fields['Count'] = $descendants;
        }

        $this->importer->set($id, new PdfDictionary($fields));

        return ['id' => $id, 'total' => 1 + $descendants];
    }

    /**
     * @param list<array{id:int,total:int}> $items
     */
    private function wireSiblings(array $items): void
    {
        $n = count($items);
        for ($i = 0; $i < $n; $i++) {
            $dict = $this->importer->get($items[$i]['id']);
            if (!$dict instanceof PdfDictionary) {
                continue;
            }
            $fields = $dict->all();
            if ($i > 0) {
                $fields['Prev'] = new PdfReference($items[$i - 1]['id'], 0);
            }
            if ($i < $n - 1) {
                $fields['Next'] = new PdfReference($items[$i + 1]['id'], 0);
            }
            $this->importer->set($items[$i]['id'], new PdfDictionary($fields));
        }
    }

    /** @param list<array{id:int,total:int}> $items */
    private function sumTotals(array $items): int
    {
        $sum = 0;
        foreach ($items as $it) {
            $sum += $it['total'];
        }
        return $sum;
    }

    /** @param list<ReaderDocument> $docs */
    private function anyOutlines(array $docs): bool
    {
        foreach ($docs as $doc) {
            $outlines = $this->outlinesOf($doc);
            if ($outlines !== null && $outlines->get('First') instanceof PdfReference) {
                return true;
            }
        }
        return false;
    }

    private function outlinesOf(ReaderDocument $doc): ?PdfDictionary
    {
        try {
            $outlines = $doc->deref($doc->catalog()->get('Outlines'));
        } catch (PdfParseException) {
            return null;
        }
        return $outlines instanceof PdfDictionary ? $outlines : null;
    }
}
