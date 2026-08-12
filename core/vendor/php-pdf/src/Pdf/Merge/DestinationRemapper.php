<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Merge;

use Dskripchenko\PhpPdf\Pdf\Reader\PdfDictionary;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfName;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfNull;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfReference;
use Dskripchenko\PhpPdf\Pdf\Reader\PdfString;
use Dskripchenko\PhpPdf\Pdf\Reader\ReaderDocument;

/**
 * Resolves the link on an annotation or outline item during merge, remapping
 * internal page destinations to the new output pages.
 *
 * External links (URI, GoToR, Launch, Named actions) are kept verbatim.
 * Internal GoTo/`/Dest` links are rewritten to the new page reference when the
 * target page is included in the output, and reported as *dangling* otherwise
 * (the caller drops the whole node). Named destinations are treated as
 * unresolvable (dropped), since the names tree is not carried into the output.
 */
final class DestinationRemapper
{
    public function __construct(private readonly ObjectImporter $importer)
    {
    }

    /**
     * @param array<int,int> $docPageMap source page object-number → new page ID
     * @return array<string,mixed>|null
     *         null            → drop the node (dangling internal link)
     *         []              → keep the node, it has no link
     *         ['A' => …]      → keep the node with this (remapped/kept) action
     *         ['Dest' => …]   → keep the node with this remapped explicit dest
     */
    public function resolveLink(ReaderDocument $doc, PdfDictionary $node, array $docPageMap): ?array
    {
        $action = $doc->deref($node->get('A'));
        if ($action instanceof PdfDictionary) {
            $s = $action->get('S');
            $kind = $s instanceof PdfName ? $s->value : '';
            if ($kind === 'GoTo') {
                $dest = $this->remapExplicitDest($doc, $action->get('D'), $docPageMap);
                return $dest === null
                    ? null
                    : ['A' => new PdfDictionary(['S' => new PdfName('GoTo'), 'D' => $dest])];
            }
            // URI / GoToR / Launch / Named / … — no internal page reference.
            return ['A' => $this->importer->importValue($action)];
        }

        $dest = $node->get('Dest');
        if ($dest !== null && !$dest instanceof PdfNull) {
            $remapped = $this->remapExplicitDest($doc, $dest, $docPageMap);
            return $remapped === null ? null : ['Dest' => $remapped];
        }

        return [];
    }

    /**
     * Remap a destination (explicit array, or a named destination resolved via
     * the document's names) to point at the new page.
     *
     * @param array<int,int> $docPageMap
     * @return list<mixed>|null null when unresolvable or dangling
     */
    private function remapExplicitDest(ReaderDocument $doc, mixed $dest, array $docPageMap): ?array
    {
        $dest = $this->toExplicitArray($doc, $dest);
        if ($dest === null || $dest === []) {
            return null;
        }
        $target = $dest[0];
        if (!$target instanceof PdfReference || !array_key_exists($target->number, $docPageMap)) {
            return null; // points at a page not included in the output
        }

        $out = [new PdfReference($docPageMap[$target->number], 0)];
        for ($i = 1, $n = count($dest); $i < $n; $i++) {
            $out[] = $this->importer->importValue($dest[$i]);
        }
        return $out;
    }

    /**
     * Reduce any destination form to an explicit `[pageRef …]` array:
     * dereference, resolve a named destination through the document's names,
     * and unwrap a `<< /D … >>` wrapper.
     *
     * @return list<mixed>|null
     */
    private function toExplicitArray(ReaderDocument $doc, mixed $dest): ?array
    {
        $dest = $doc->deref($dest);

        // Named destination: /Dest as a name (legacy) or byte string (name tree).
        if ($dest instanceof PdfName) {
            $dest = $doc->deref($doc->namedDestinations()[$dest->value] ?? null);
        } elseif ($dest instanceof PdfString) {
            $dest = $doc->deref($doc->namedDestinations()[$dest->bytes] ?? null);
        }

        // A destination may be wrapped as << /D [ … ] >>.
        if ($dest instanceof PdfDictionary) {
            $dest = $doc->deref($dest->get('D'));
        }

        return is_array($dest) ? array_values($dest) : null;
    }
}
