<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

use Dskripchenko\PhpPdf\Style\Orientation;
use Dskripchenko\PhpPdf\Style\PaperSize;

/**
 * High-level PDF document — primary entry point.
 *
 * Hides low-level Writer/ContentStream details: client adds pages, draws on
 * them, and emits bytes.
 *
 *   $doc = Document::new(PaperSize::A4);
 *   $page = $doc->addPage();
 *   $page->showText('Hello, world!', 72, 720, StandardFont::TimesRoman, 12);
 *   $bytes = $doc->toBytes();
 *
 * Document is responsible for:
 *  - Creating the Writer and orchestrating its low-level API.
 *  - Registering unique fonts/images (deduplicated by identity/content hash).
 *  - Emitting Catalog + Pages tree + per-page Page object + content stream.
 *  - Cross-reference table + trailer + EOF.
 */
final class Document
{
    /** @var list<Page> */
    private array $pages = [];

    /**
     * Imported foreign pages registered as Form XObjects (FPDI-style), keyed by
     * the form instance. Each holds the foreign resource-closure objects (local
     * id → value tree) and the form's /Resources value tree referencing them.
     *
     * @var \SplObjectStorage<PdfFormXObject, array{objects: array<int,mixed>, resources: \Dskripchenko\PhpPdf\Pdf\Reader\PdfDictionary}>
     */
    private \SplObjectStorage $importedForms;

    /**
     * Named destinations for internal links and bookmarks.
     *
     * @var array<string, array{page: Page, x: float, y: float}>
     */
    private array $namedDestinations = [];

    /**
     * Outline (bookmarks panel) entries as a flat list. Tree structure is
     * reconstructed in toBytes() based on level.
     *
     * @var list<array{level: int, title: string, page: Page, x: float, y: float}>
     */
    private array $outlineEntries = [];

    private string $pdfVersion = '1.7';

    /** Emit xref as XRef stream object (PDF 1.5+) instead of classic table. */
    private bool $useXrefStream = false;

    /** Pack non-stream objects into an Object Stream (PDF 1.5+). */
    private bool $useObjectStreams = false;

    /** Page count threshold for switching from flat → balanced tree. */
    private const PAGE_TREE_THRESHOLD = 32;

    /** Max children per /Pages node (PDF spec recommends ≤ 16-20). */
    private const PAGE_TREE_FANOUT = 16;

    /**
     * Build a balanced Page Tree structure.
     *
     * PDF spec §7.7.3.3 recommends a balanced /Pages tree for efficient
     * reader navigation. A flat tree (default) is acceptable, but a
     * balanced tree is noticeably faster for documents > 100 pages.
     *
     * Strategy: single intermediate level with adaptive chunk size. Up to
     * FANOUT² ≈ 256 pages fits with chunkSize=FANOUT (16); larger documents
     * use larger chunks to keep root /Kids ≤ FANOUT.
     *
     * @param  list<int>  $pageIds
     * @return array{parentOf: list<int>, rootKids: list<int>, intermediates: list<array{id: int, kids: list<int>, count: int}>}
     */
    private function buildPageTree(array $pageIds, int $rootId, Writer $writer): array
    {
        $n = count($pageIds);

        // Small documents — flat tree, root is direct parent of all pages.
        if ($n <= self::PAGE_TREE_THRESHOLD) {
            return [
                'parentOf' => array_fill(0, $n, $rootId),
                'rootKids' => $pageIds,
                'intermediates' => [],
            ];
        }

        // Adaptive chunk size — each chunk gets ~FANOUT pages but root /Kids ≤ FANOUT.
        $fanout = self::PAGE_TREE_FANOUT;
        $chunkSize = max($fanout, (int) ceil($n / $fanout));

        $parentOf = array_fill(0, $n, 0);
        $rootKids = [];
        $intermediates = [];

        $offset = 0;
        while ($offset < $n) {
            $end = min($offset + $chunkSize, $n);
            $intermediateId = $writer->reserveObject();
            for ($i = $offset; $i < $end; $i++) {
                $parentOf[$i] = $intermediateId;
            }
            $rootKids[] = $intermediateId;
            $intermediates[] = [
                'id' => $intermediateId,
                'kids' => array_slice($pageIds, $offset, $end - $offset),
                'count' => $end - $offset,
            ];
            $offset = $end;
        }

        return [
            'parentOf' => $parentOf,
            'rootKids' => $rootKids,
            'intermediates' => $intermediates,
        ];
    }

    /**
     * Metadata fields (Title, Author, Subject, Keywords, Creator, Producer).
     * Shown in the reader's "Document Properties" dialog. All optional;
     * emitted in /Info dict whenever at least one entry is set.
     *
     * @var array<string, string>
     */
    private array $metadata = [];

    /**
     * Optional encryption config. When set, emit /Encrypt in trailer and
     * encrypt all stream content per object.
     */
    private ?Encryption $encryption = null;

    /** Optional PKCS#7 signing config (applied during toBytes). */
    private ?SignatureConfig $signatureConfig = null;

    /** @var list<PdfLayer> Optional Content Groups (layers). */
    private array $layers = [];

    /** @var array<string, string> Document-level /AA actions (event → JS). */
    private array $documentActions = [];

    /** Reserved signature dict ID, set transiently during emit(). */
    private ?int $signatureDictId = null;

    /** Tracks whether /V was already attached to one signature widget. */
    private bool $signatureFieldLinked = false;

    /** Reserved Helvetica font ID for AcroForm appearance streams. */
    private ?int $appearanceFontId = null;

    /** Reserved ZapfDingbats font ID for checkbox/radio glyphs. */
    private ?int $appearanceZapfId = null;

    /** @var list<int> Form field object IDs collected during toBytes. */
    private array $collectedFormFieldIds = [];

    /** @var list<int> Field IDs with /AA /C calculate scripts (for AcroForm /CO). */
    private array $calculatedFieldIds = [];

    /** PDF/A configuration (mutually exclusive with PDF/X and encryption). */
    private ?PdfAConfig $pdfA = null;

    /** PDF/X print conformance config (mutually exclusive with PDF/A). */
    private ?PdfXConfig $pdfX = null;

    /** Tagged PDF mode (accessibility). */
    private bool $tagged = false;

    /**
     * Open action — applied when the document is opened.
     * Modes:
     *  - 'fit-page' — zoom to fit entire page.
     *  - 'fit-width' — zoom to fit page width.
     *  - 'actual-size' — 100%.
     *  - 'xyz' (default) — explicit x/y/zoom or null = reader default.
     *
     * @var array<string, mixed>|null  Form: ['mode' => string, 'page' => int (1-based), ...].
     */
    private ?array $openAction = null;

    /**
     * Page display mode on open.
     * Values: 'use-none' (default), 'use-outlines', 'use-thumbs',
     *         'use-oc' (optional content), 'full-screen'.
     */
    private ?string $pageMode = null;

    /**
     * Page layout mode.
     * Values: 'single-page' (default), 'one-column', 'two-column-left',
     *         'two-column-right', 'two-page-left', 'two-page-right'.
     */
    private ?string $pageLayout = null;

    /**
     * /ViewerPreferences entries.
     *
     * @var array<string, mixed>
     */
    private array $viewerPreferences = [];

    /** BCP 47 language tag for /Lang in Catalog. */
    private ?string $lang = null;

    /**
     * Custom struct role aliases — maps non-standard struct type names
     * to standard PDF/UA roles.
     *
     * @var array<string, string>  custom → standard
     */
    private array $structRoleMap = [];

    /**
     * Configure role map for custom struct types.
     *
     * @param  array<string, string>  $roleMap
     */
    public function setStructRoleMap(array $roleMap): self
    {
        $this->structRoleMap = $roleMap;

        return $this;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Configure viewer preferences. Keys: hideToolbar, hideMenubar,
     * hideWindowUI, fitWindow, centerWindow, displayDocTitle (bool);
     * direction ('L2R'|'R2L'); printScaling ('None'|'AppDefault');
     * duplex ('Simplex'|'DuplexFlipShortEdge'|'DuplexFlipLongEdge').
     *
     * @param  array<string, mixed>  $prefs
     */
    public function setViewerPreferences(array $prefs): self
    {
        $this->viewerPreferences = $prefs;

        return $this;
    }

    /**
     * Page label ranges per ISO 32000-1 §12.4.2.
     *
     * @var list<array{startPage: int, style?: string, prefix?: string, firstNumber?: int}>
     */
    private array $pageLabelRanges = [];

    /**
     * Configure page label numbering style per page range.
     * Styles: 'decimal' (1, 2, 3), 'upper-roman' (I, II, III),
     * 'lower-roman' (i, ii, iii), 'upper-alpha' (A, B, C),
     * 'lower-alpha' (a, b, c).
     *
     * @param  list<array{startPage: int, style?: string, prefix?: string, firstNumber?: int}>  $ranges
     */
    public function setPageLabels(array $ranges): self
    {
        $this->pageLabelRanges = $ranges;

        return $this;
    }

    /**
     * Set the open action — zoom + page on document open.
     */
    public function setOpenAction(string $mode = 'fit-page', int $pageIndex = 1, ?float $x = null, ?float $y = null, ?float $zoom = null): self
    {
        $this->openAction = [
            'mode' => $mode,
            'page' => $pageIndex,
            'x' => $x, 'y' => $y, 'zoom' => $zoom,
        ];

        return $this;
    }

    public function setPageMode(string $mode): self
    {
        $this->pageMode = $mode;

        return $this;
    }

    public function setPageLayout(string $layout): self
    {
        $this->pageLayout = $layout;

        return $this;
    }

    /**
     * Embedded files (attachments).
     *
     * @var list<array{name: string, bytes: string, mimeType: ?string, description: ?string}>
     */
    private array $embeddedFiles = [];

    /**
     * Attach a file to the PDF. The file does not appear in page content;
     * it is shown in the reader's attachments panel (Acrobat, Foxit, etc.).
     */
    public function attachFile(string $name, string $bytes, ?string $mimeType = null, ?string $description = null): self
    {
        $this->embeddedFiles[] = [
            'name' => $name,
            'bytes' => $bytes,
            'mimeType' => $mimeType,
            'description' => $description,
        ];

        return $this;
    }

    /**
     * Struct elements collected during rendering — emitted in StructTreeRoot/K.
     * Leaf entries carry an mcid; container entries (Table/TR/TD/L/LI)
     * carry mcid = null plus a list of child element indexes. Hierarchy
     * lives in the structure tree — marked-content sequences never nest.
     *
     * @var list<array{type: string, mcid: ?int, page: Page, altText?: ?string, parent: ?int, children: list<int>, objr?: int}>
     */
    private array $structElements = [];

    /**
     * Stack of open container element indexes (renderer-driven).
     *
     * @var list<int>
     */
    private array $structContainerStack = [];

    /**
     * structElement index → /StructParent number tree key for tagged Link
     * annotations. Used to wire the /Link annot back to its StructElem in
     * the ParentTree.
     *
     * @var array<int, int>
     */
    private array $structParentLinkKeys = [];

    /**
     * Enable Tagged PDF (accessibility).
     *
     * Adds /MarkInfo /Marked true + /StructTreeRoot to the Catalog. Each
     * rendered paragraph is wrapped in BDC /P << /MCID N >> ... EMC content
     * marking. The structure tree contains a single /Document root with a
     * flat list of /P children.
     *
     * NOT full PDF/UA compliance:
     *  - All blocks are tagged as /P (no H1/H2/Table/Caption distinction).
     *  - Alt text for images / figures is not emitted.
     *  - Reading order is preserved but not explicit via /StructParents.
     */
    public function enableTagged(): self
    {
        $this->tagged = true;

        return $this;
    }

    public function isTagged(): bool
    {
        return $this->tagged;
    }

    /**
     * @internal Used by the Engine to register leaf struct elements
     * (the ones owning a marked-content sequence).
     */
    public function addStructElement(string $type, int $mcid, Page $page, ?string $altText = null): void
    {
        $parent = $this->structContainerStack === []
            ? null
            : $this->structContainerStack[count($this->structContainerStack) - 1];
        $idx = count($this->structElements);
        $this->structElements[] = [
            'type' => $type, 'mcid' => $mcid, 'page' => $page,
            'altText' => $altText, 'parent' => $parent, 'children' => [],
        ];
        if ($parent !== null) {
            $this->structElements[$parent]['children'][] = $idx;
        }
    }

    /**
     * @internal Open a grouping struct element (Table, TR, TD, L, LI...).
     * Containers have no marked content of their own — their /K is the
     * list of child elements, so MCID sequences never nest.
     */
    public function beginStructContainer(string $type, Page $page): void
    {
        $parent = $this->structContainerStack === []
            ? null
            : $this->structContainerStack[count($this->structContainerStack) - 1];
        $idx = count($this->structElements);
        $this->structElements[] = [
            'type' => $type, 'mcid' => null, 'page' => $page,
            'altText' => null, 'parent' => $parent, 'children' => [],
        ];
        if ($parent !== null) {
            $this->structElements[$parent]['children'][] = $idx;
        }
        $this->structContainerStack[] = $idx;
    }

    /**
     * @internal Close the innermost grouping struct element.
     */
    public function endStructContainer(): void
    {
        array_pop($this->structContainerStack);
    }

    /**
     * Enable PDF/A compliance. Conformance 'A' (accessibility) auto-enables
     * Tagged PDF.
     */
    public function enablePdfA(PdfAConfig $config): self
    {
        if ($this->encryption !== null) {
            throw new \LogicException('PDF/A disallows encryption — call enablePdfA() before encrypt(), or omit encrypt().');
        }
        if ($this->pdfX !== null) {
            throw new \LogicException('Cannot combine PDF/A and PDF/X — choose one variant.');
        }
        $this->pdfA = $config;
        $this->pdfVersion = '1.4';
        if ($config->conformance === PdfAConfig::CONFORMANCE_A && ! $this->tagged) {
            $this->enableTagged();
        }

        return $this;
    }

    /**
     * Enable PDF/X-1a/X-3/X-4 print conformance.
     *
     * Emits OutputIntent with /S /GTS_PDFX, /Trapped key in /Info, and XMP
     * metadata with pdfx: namespace markers.
     *
     * The caller is responsible for content-level compliance:
     *  - All fonts embedded (default behaviour).
     *  - No transparency for X-1a/X-3 variants.
     *  - CMYK colorspace for X-1a (we render RGB by default — X-1a is not
     *    strictly compliant without CMYK conversion).
     *
     * Mutually exclusive with PDF/A and encryption.
     */
    public function enablePdfX(PdfXConfig $config): self
    {
        if ($this->encryption !== null) {
            throw new \LogicException('PDF/X disallows encryption.');
        }
        if ($this->pdfA !== null) {
            throw new \LogicException('Cannot combine PDF/X and PDF/A — choose one variant.');
        }
        $this->pdfX = $config;
        // PDF/X-4 requires PDF 1.6+; X-1a/X-3 require PDF 1.4+.
        if ($config->variant === PdfXConfig::VARIANT_X4) {
            if (version_compare($this->pdfVersion, '1.6', '<')) {
                $this->pdfVersion = '1.6';
            }
        } elseif (version_compare($this->pdfVersion, '1.4', '<')) {
            $this->pdfVersion = '1.4';
        }

        return $this;
    }

    /**
     * Enable PDF encryption.
     *
     * RC4-128 (V2 R3) — default, widely supported including legacy readers.
     * AES-128 (V4 R4) — modern, supersedes RC4. Requires the openssl ext.
     * AES-256 (V5 R5/R6) — PDF 1.7 ExtLvl 8 / PDF 2.0 (ISO 32000-2).
     */
    public function encrypt(
        string $userPassword,
        ?string $ownerPassword = null,
        int $permissions = Encryption::PERM_PRINT | Encryption::PERM_COPY | Encryption::PERM_PRINT_HIGH,
        EncryptionAlgorithm $algorithm = EncryptionAlgorithm::Rc4_128,
    ): self {
        if ($this->pdfA !== null) {
            throw new \LogicException('PDF/A disallows encryption');
        }
        $this->encryption = new Encryption($userPassword, $ownerPassword, $permissions, $algorithm);
        // PDF 1.6 required for AES-128; 1.7 for AES-256 V5.
        if ($algorithm === EncryptionAlgorithm::Aes_128 && version_compare($this->pdfVersion, '1.6', '<')) {
            $this->pdfVersion = '1.6';
        }
        if ($algorithm === EncryptionAlgorithm::Aes_256 && version_compare($this->pdfVersion, '1.7', '<')) {
            $this->pdfVersion = '1.7';
        }
        // R6 — PDF 2.0 (ISO 32000-2). Bump version unconditionally.
        if ($algorithm === EncryptionAlgorithm::Aes_256_R6) {
            $this->pdfVersion = '2.0';
        }

        return $this;
    }

    /**
     * Enable PKCS#7 detached signing.
     *
     * Requires at least one form field of type 'signature' to be added —
     * it will receive /V referencing the signature dictionary.
     *
     * The PDF bytes are patched in toBytes() after assembly:
     *   1. /ByteRange [a b c d] computed from actual /Contents position.
     *   2. Bytes outside /Contents are hashed and signed via openssl_pkcs7_sign.
     *   3. /Contents placeholder filled with hex-encoded DER PKCS#7 envelope.
     */
    public function sign(SignatureConfig $config): self
    {
        $this->signatureConfig = $config;

        return $this;
    }

    /**
     * Register a layer (Optional Content Group). The returned instance is
     * passed to Page::beginLayer() to mark content.
     */
    public function addLayer(string $name, bool $defaultVisible = true, string $intent = 'View'): PdfLayer
    {
        $layer = new PdfLayer($name, $defaultVisible, $intent);
        $this->layers[] = $layer;

        return $layer;
    }

    /**
     * Register a document-level Additional Action.
     *
     * Events (PDF spec §12.6.3 Table 197):
     *  - WillClose (WC), WillSave (WS), DidSave (DS),
     *    WillPrint (WP), DidPrint (DP).
     *
     * For document-open use {@see setOpenAction()} (separate /OpenAction entry).
     *
     * Emits /AA << /WC <<...>> /WS <<...>> ... >> in Catalog.
     */
    public function setDocumentAction(string $event, string $script): self
    {
        $valid = ['WC', 'WS', 'DS', 'WP', 'DP'];
        if (! in_array($event, $valid, true)) {
            throw new \InvalidArgumentException('Document action event must be one of: ' . implode(', ', $valid));
        }
        $this->documentActions[$event] = $script;

        return $this;
    }

    /**
     * @return list<PdfLayer>
     *
     * @internal
     */
    public function layers(): array
    {
        return $this->layers;
    }

    /**
     * @param  array{0: float, 1: float}|null  $defaultCustomDimensionsPt
     */
    public function __construct(
        public PaperSize $defaultPaperSize = PaperSize::A4,
        public Orientation $defaultOrientation = Orientation::Portrait,
        public ?array $defaultCustomDimensionsPt = null,
        /**
         * When true, content streams are compressed via FlateDecode (~3-5×
         * smaller for text-heavy documents). Set false for debug inspection
         * of raw streams.
         */
        public bool $compressStreams = true,
    ) {
        $this->importedForms = new \SplObjectStorage();
    }

    /**
     * Register a Form XObject imported from an existing PDF page, together with
     * its foreign resource closure. Called by the import layer
     * ({@see \Dskripchenko\PhpPdf\Pdf\Merge\PageImporter}); the objects are
     * emitted — with references renumbered into this document — when the form
     * is written. The returned form is used via {@see Page::useFormXObject()}.
     *
     * @param array<int,mixed> $localObjects local id → parsed value tree
     */
    public function registerImportedForm(
        PdfFormXObject $form,
        array $localObjects,
        \Dskripchenko\PhpPdf\Pdf\Reader\PdfDictionary $resources,
    ): void {
        $this->importedForms[$form] = ['objects' => $localObjects, 'resources' => $resources];
    }

    /**
     * Emit an imported form's foreign resource-closure objects into the writer
     * (references renumbered) and return its serialized /Resources body.
     * Non-imported forms get an empty resource dictionary.
     */
    private function emitImportedFormResources(PdfFormXObject $form, Writer $writer): string
    {
        if (!isset($this->importedForms[$form])) {
            return '<< >>';
        }
        $import = $this->importedForms[$form];

        $map = [];
        foreach (array_keys($import['objects']) as $localId) {
            $map[$localId] = $writer->reserveObject();
        }
        $serializer = new \Dskripchenko\PhpPdf\Pdf\Reader\PdfValueSerializer(
            static fn (int $n): int => $map[$n] ?? $n,
        );
        foreach ($import['objects'] as $localId => $value) {
            $writer->setObject($map[$localId], $serializer->encode($value));
        }

        return $serializer->encode($import['resources']);
    }

    public static function new(
        PaperSize $defaultPaperSize = PaperSize::A4,
        Orientation $defaultOrientation = Orientation::Portrait,
        bool $compressStreams = true,
    ): self {
        return new self(
            defaultPaperSize: $defaultPaperSize,
            defaultOrientation: $defaultOrientation,
            compressStreams: $compressStreams,
        );
    }

    public function pdfVersion(string $version): self
    {
        $this->pdfVersion = $version;

        return $this;
    }

    /**
     * Enable XRef stream cross-reference table (PDF 1.5+).
     *
     * Replaces the classic `xref...trailer` keywords with a binary-packed
     * FlateDecode object — ~50% smaller metadata footprint. Auto-bumps PDF
     * version to 1.5+ if currently below. Incompatible with PKCS#7 signing
     * (the signing path keeps classic xref for simpler /ByteRange handling;
     * when both are configured, classic xref wins).
     */
    public function useXrefStream(bool $enabled = true): self
    {
        $this->useXrefStream = $enabled;
        if ($enabled && version_compare($this->pdfVersion, '1.5', '<')) {
            $this->pdfVersion = '1.5';
        }

        return $this;
    }

    /**
     * Enable Object Streams (PDF 1.5+) — pack uncompressed dict objects
     * (catalog, page tree, info, non-stream font dicts, etc.) into one
     * FlateDecode-compressed stream. Saves ~15-30% output size on documents
     * with many small objects.
     *
     * Implies XRef streams (type-2 entries for compressed objects require
     * the new xref encoding). Auto-enables useXrefStream if not set and
     * bumps PDF version to 1.5+.
     *
     * Auto-disabled when encryption or PKCS#7 signing is configured.
     */
    public function useObjectStreams(bool $enabled = true): self
    {
        $this->useObjectStreams = $enabled;
        if ($enabled) {
            $this->useXrefStream = true;
            if (version_compare($this->pdfVersion, '1.5', '<')) {
                $this->pdfVersion = '1.5';
            }
        }

        return $this;
    }

    /**
     * Set PDF metadata (/Info dict). All parameters are optional; only the
     * non-null fields are emitted. CreationDate is auto-populated if not
     * provided.
     */
    public function metadata(
        ?string $title = null,
        ?string $author = null,
        ?string $subject = null,
        ?string $keywords = null,
        ?string $creator = null,
        ?string $producer = null,
        ?\DateTimeInterface $creationDate = null,
    ): self {
        if ($title !== null) {
            $this->metadata['Title'] = $title;
        }
        if ($author !== null) {
            $this->metadata['Author'] = $author;
        }
        if ($subject !== null) {
            $this->metadata['Subject'] = $subject;
        }
        if ($keywords !== null) {
            $this->metadata['Keywords'] = $keywords;
        }
        if ($creator !== null) {
            $this->metadata['Creator'] = $creator;
        }
        if ($producer !== null) {
            $this->metadata['Producer'] = $producer;
        }
        if ($creationDate !== null) {
            $this->metadata['CreationDate'] = $this->formatPdfDate($creationDate);
        }

        return $this;
    }

    /**
     * Component count for an ICC profile's colour space (header bytes
     * 16..19): 'RGB ' → 3, 'CMYK' → 4, 'GRAY' → 1. Used for the /N key
     * of embedded DestOutputProfile streams.
     */
    private static function iccComponents(string $iccBytes): int
    {
        return match (substr($iccBytes, 16, 4)) {
            'CMYK' => 4,
            'GRAY' => 1,
            default => 3,
        };
    }

    private function formatPdfDate(\DateTimeInterface $dt): string
    {
        // PDF date format: D:YYYYMMDDHHmmSS+TZ'mm'
        return 'D:'.$dt->format('YmdHis').'+00\'00\'';
    }

    /**
     * Add a new page. When paperSize/orientation are omitted, the document-
     * level defaults are used.
     *
     * @param  array{0: float, 1: float}|null  $customDimensionsPt
     */
    public function addPage(
        ?PaperSize $paperSize = null,
        ?Orientation $orientation = null,
        ?array $customDimensionsPt = null,
    ): Page {
        $page = new Page(
            $paperSize ?? $this->defaultPaperSize,
            $orientation ?? $this->defaultOrientation,
            $customDimensionsPt ?? $this->defaultCustomDimensionsPt,
        );
        $this->pages[] = $page;

        return $page;
    }

    /**
     * @return list<Page>
     */
    public function pages(): array
    {
        return $this->pages;
    }

    public function pageCount(): int
    {
        return count($this->pages);
    }

    /**
     * Register a named destination — position (x, y) on $page becomes the
     * jump target for internal links with this name. Last-write wins.
     */
    public function registerDestination(string $name, Page $page, float $x, float $y): self
    {
        $this->namedDestinations[$name] = ['page' => $page, 'x' => $x, 'y' => $y];

        return $this;
    }

    /**
     * @return array<string, array{page: Page, x: float, y: float}>
     */
    public function namedDestinations(): array
    {
        return $this->namedDestinations;
    }

    /**
     * Register an outline entry for the bookmarks panel. $level (1..N)
     * determines nesting: level 1 = top-level, level 2 = child of the
     * latest level-1 entry, and so on.
     */
    public function registerOutlineEntry(
        int $level, string $title, Page $page, float $x, float $y,
        ?string $color = null, bool $bold = false, bool $italic = false,
    ): self {
        $this->outlineEntries[] = compact(
            'level', 'title', 'page', 'x', 'y', 'color', 'bold', 'italic',
        );

        return $this;
    }

    /**
     * @return list<array{level: int, title: string, page: Page, x: float, y: float}>
     */
    public function outlineEntries(): array
    {
        return $this->outlineEntries;
    }

    /**
     * Serialize the document to PDF bytes.
     */
    public function toBytes(): string
    {
        return $this->buildWriter()->toBytes();
    }

    /**
     * Build a configured Writer ready to emit. Shared between toBytes()
     * and toStream().
     */
    private function buildWriter(): Writer
    {
        if ($this->pages === []) {
            // Empty document — add a blank page so the PDF is valid
            // (the spec requires ≥ 1 page in the page tree).
            $this->addPage();
        }

        // XRef streams auto-disabled with PKCS#7 signing — the signing path
        // patches /ByteRange + /Contents in classic xref layout.
        $useXref = $this->useXrefStream && $this->signatureConfig === null;
        $useObjStm = $this->useObjectStreams
            && $useXref
            && $this->encryption === null
            && $this->signatureConfig === null;
        $writer = new Writer(
            $this->pdfVersion,
            useXrefStream: $useXref,
            useObjectStreams: $useObjStm,
        );

        // 1. Reserve top-level IDs.
        $catalogId = $writer->reserveObject();
        $pagesId = $writer->reserveObject();

        // Reserve signature dictionary ID upfront so widget /V can
        // reference it during field emission.
        $this->signatureDictId = null;
        $this->signatureFieldLinked = false;
        if ($this->signatureConfig !== null) {
            $this->signatureDictId = $writer->reserveObject();
        }

        // Reset appearance font IDs — assigned lazily in buildAppearance*.
        $this->appearanceFontId = null;
        $this->appearanceZapfId = null;

        // 2. Register all unique fonts/images via identity dedup.
        //    Standard fonts: one PDF object per unique StandardFont enum.
        //    Embedded fonts: one object graph per PdfFont instance.
        //    Images: one XObject per PdfImage instance.

        // Emit OCG objects upfront so page Properties references them by ID.
        /** @var \SplObjectStorage<PdfLayer, int> */
        $layerObjectIds = new \SplObjectStorage;
        foreach ($this->layers as $layer) {
            $layerObjectIds[$layer] = $writer->addObject($layer->dictBody());
        }

        // Map StandardFont enum → object ID (single registration).
        /** @var array<string, int> */
        $standardFontObjectIds = [];
        foreach ($this->pages as $page) {
            foreach ($page->standardFonts() as $sf) {
                if (! isset($standardFontObjectIds[$sf->value])) {
                    $standardFontObjectIds[$sf->value] = $writer->addObject($sf->pdfDictionary());
                }
            }
        }

        // Embedded PdfFonts — each PdfFont dispatches its own registration
        // and is idempotent (double registerWith returns the same id).
        // Map PdfFont instance → object ID.
        /** @var \SplObjectStorage<PdfFont, int> */
        $embeddedFontObjectIds = new \SplObjectStorage;
        foreach ($this->pages as $page) {
            foreach ($page->embeddedFonts() as $f) {
                if (! isset($embeddedFontObjectIds[$f])) {
                    $embeddedFontObjectIds[$f] = $f->registerWith($writer, $this->compressStreams);
                }
            }
        }

        // Images. Dedup by content hash (not just instance); the same file
        // loaded twice produces a single XObject.
        /** @var \SplObjectStorage<\Dskripchenko\PhpPdf\Image\PdfImage, int> */
        $imageObjectIds = new \SplObjectStorage;
        /** @var array<string, int> hash → existing object ID */
        $imageHashMap = [];
        foreach ($this->pages as $page) {
            foreach ($page->images() as $img) {
                if (isset($imageObjectIds[$img])) {
                    continue;
                }
                $hash = md5($img->imageData);
                if (isset($imageHashMap[$hash])) {
                    $imageObjectIds[$img] = $imageHashMap[$hash];

                    continue;
                }
                $id = $img->registerWith($writer);
                $imageObjectIds[$img] = $id;
                $imageHashMap[$hash] = $id;
            }
        }

        // Form XObjects — shared content streams referenced by /Do.
        // Identity-dedup per instance (same PdfFormXObject used on
        // multiple pages → single XObject in output).
        /** @var \SplObjectStorage<PdfFormXObject, int> */
        $formXObjectIds = new \SplObjectStorage;
        foreach ($this->pages as $page) {
            foreach ($page->formXObjects() as $form) {
                if (isset($formXObjectIds[$form])) {
                    continue;
                }
                $resourcesBody = $this->emitImportedFormResources($form, $writer);
                $body = $form->contentStream;
                if ($this->compressStreams && $body !== '') {
                    $compressed = (string) gzcompress($body, 6);
                    $formId = $writer->addObject(sprintf(
                        "<< %s /Resources %s /Length %d /Filter /FlateDecode >>\nstream\n%s\nendstream",
                        $form->dictHead(),
                        $resourcesBody,
                        strlen($compressed),
                        $compressed,
                    ));
                } else {
                    $formId = $writer->addObject(sprintf(
                        "<< %s /Resources %s /Length %d >>\nstream\n%s\nendstream",
                        $form->dictHead(),
                        $resourcesBody,
                        strlen($body),
                        $body,
                    ));
                }
                $formXObjectIds[$form] = $formId;
            }
        }

        // 3. Reserve page IDs upfront (needed for internal-link Dest
        //    references — annotation objects may reference pages before
        //    those pages are emitted).
        $pageIds = [];
        foreach ($this->pages as $i => $page) {
            $pageIds[$i] = $writer->reserveObject();
        }

        // Map Page instance → object ID (for annotation /Dest references).
        $pageObjectIdMap = new \SplObjectStorage;
        foreach ($this->pages as $i => $page) {
            $pageObjectIdMap[$page] = $pageIds[$i];
        }

        // Balanced Page Tree for documents with many pages. Threshold 32:
        // < 32 pages = flat tree; otherwise chunked intermediate level.
        // Each page's /Parent points to its immediate /Pages node.
        $pageTree = $this->buildPageTree($pageIds, $pagesId, $writer);

        // 4. Build Page objects + content streams + annotations.
        foreach ($this->pages as $i => $page) {
            $contentStreamBody = $page->buildContentStream();
            if ($this->compressStreams && $contentStreamBody !== '') {
                $compressed = (string) gzcompress($contentStreamBody, 6);
                $contentId = $writer->addObject(sprintf(
                    "<< /Length %d /Filter /FlateDecode >>\nstream\n%s\nendstream",
                    strlen($compressed),
                    $compressed,
                ));
            } else {
                $contentId = $writer->addObject(sprintf(
                    "<< /Length %d >>\nstream\n%s\nendstream",
                    strlen($contentStreamBody),
                    $contentStreamBody,
                ));
            }

            // Build Page /Resources dict for the fonts/images used.
            $resourcesFont = '';
            foreach ($page->standardFonts() as $name => $sf) {
                $resourcesFont .= sprintf(' /%s %d 0 R', $name, $standardFontObjectIds[$sf->value]);
            }
            foreach ($page->embeddedFonts() as $name => $f) {
                $resourcesFont .= sprintf(' /%s %d 0 R', $name, $embeddedFontObjectIds[$f]);
            }
            $resourcesXObj = '';
            foreach ($page->images() as $name => $img) {
                $resourcesXObj .= sprintf(' /%s %d 0 R', $name, $imageObjectIds[$img]);
            }
            // Form XObjects share /XObject namespace on the page.
            foreach ($page->formXObjects() as $name => $form) {
                $resourcesXObj .= sprintf(' /%s %d 0 R', $name, $formXObjectIds[$form]);
            }

            // ExtGState objects (opacity, etc.). Each ExtGState is a
            // separate PDF object, referenced from page /Resources.
            $resourcesExtGState = '';
            foreach ($page->extGStates() as $name => $gs) {
                $gsId = $writer->addObject($gs->toDictBody());
                $resourcesExtGState .= sprintf(' /%s %d 0 R', $name, $gsId);
            }

            // Pattern objects — emit Function (Type 2 or Type 3 stitching
            // with sub-functions) + Shading + Pattern.
            $resourcesPattern = '';
            foreach ($page->patterns() as $name => $pattern) {
                $fn = $pattern->shading->function;
                if ($fn instanceof \Dskripchenko\PhpPdf\Pdf\PdfStitchingFunction) {
                    $subIds = [];
                    foreach ($fn->subFunctions as $sub) {
                        $subIds[] = $writer->addObject($sub->toDictBody());
                    }
                    $funcId = $writer->addObject($fn->toDictBody($subIds));
                } else {
                    $funcId = $writer->addObject($fn->toDictBody());
                }
                $shadingId = $writer->addObject($pattern->shading->toDictBody($funcId));
                $patternId = $writer->addObject($pattern->toDictBody($shadingId));
                $resourcesPattern .= sprintf(' /%s %d 0 R', $name, $patternId);
            }
            // Tiling Patterns (Type 1) — stream-bearing pattern objects
            // emitted in the same /Pattern resource namespace.
            foreach ($page->tilingPatterns() as $name => $tp) {
                $body = $tp->contentStream;
                if ($this->compressStreams && $body !== '') {
                    $compressed = (string) gzcompress($body, 6);
                    $tpId = $writer->addObject(sprintf(
                        "<< %s /Length %d /Filter /FlateDecode >>\nstream\n%s\nendstream",
                        $tp->dictHead(),
                        strlen($compressed),
                        $compressed,
                    ));
                } else {
                    $tpId = $writer->addObject(sprintf(
                        "<< %s /Length %d >>\nstream\n%s\nendstream",
                        $tp->dictHead(),
                        strlen($body),
                        $body,
                    ));
                }
                $resourcesPattern .= sprintf(' /%s %d 0 R', $name, $tpId);
            }

            // /Properties for Optional Content references (`/OC /name BDC`).
            $resourcesProperties = '';
            foreach ($page->layerProperties() as $name => $layer) {
                if (! isset($layerObjectIds[$layer])) {
                    continue;
                }
                $resourcesProperties .= sprintf(' /%s %d 0 R', $name, $layerObjectIds[$layer]);
            }

            $resourcesParts = [];
            if ($resourcesFont !== '') {
                $resourcesParts[] = '/Font <<'.$resourcesFont.' >>';
            }
            if ($resourcesXObj !== '') {
                $resourcesParts[] = '/XObject <<'.$resourcesXObj.' >>';
            }
            if ($resourcesExtGState !== '') {
                $resourcesParts[] = '/ExtGState <<'.$resourcesExtGState.' >>';
            }
            if ($resourcesPattern !== '') {
                $resourcesParts[] = '/Pattern <<'.$resourcesPattern.' >>';
            }
            if ($resourcesProperties !== '') {
                $resourcesParts[] = '/Properties <<'.$resourcesProperties.' >>';
            }
            $resourcesDict = $resourcesParts === []
                ? '<< >>'
                : '<< '.implode(' ', $resourcesParts).' >>';

            // Annotations: emit /Annot objects + reference array.
            $annotIds = [];
            foreach ($page->linkAnnotations() as $ann) {
                $rect = sprintf(
                    '[%s %s %s %s]',
                    $this->fmt($ann['x1']), $this->fmt($ann['y1']),
                    $this->fmt($ann['x2']), $this->fmt($ann['y2']),
                );

                // Reserve /StructParent number for tagged links — counter
                // starts after page indices. The annotation dict includes
                // /StructParent N; ParentTree maps N → struct elem ID.
                $structParentPart = '';
                $structParentKey = -1;
                if ($this->tagged) {
                    $structParentKey = count($this->pages) + count($this->structParentLinkKeys);
                    $structParentPart = sprintf(' /StructParent %d', $structParentKey);
                }
                $body = match ($ann['kind']) {
                    'uri' => sprintf(
                        '<< /Type /Annot /Subtype /Link /Rect %s '
                        .'/Border [0 0 0] /A << /S /URI /URI %s >>%s >>',
                        $rect,
                        $this->pdfString($ann['target']),
                        $structParentPart,
                    ),
                    'named' => sprintf(
                        '<< /Type /Annot /Subtype /Link /Rect %s '
                        .'/Border [0 0 0] /A << /Type /Action /S /Named /N /%s >>%s >>',
                        $rect,
                        $ann['target'],
                        $structParentPart,
                    ),
                    'javascript' => sprintf(
                        '<< /Type /Annot /Subtype /Link /Rect %s '
                        .'/Border [0 0 0] /A << /Type /Action /S /JavaScript /JS %s >>%s >>',
                        $rect,
                        $this->pdfString($ann['target']),
                        $structParentPart,
                    ),
                    'launch' => sprintf(
                        '<< /Type /Annot /Subtype /Link /Rect %s '
                        .'/Border [0 0 0] /A << /Type /Action /S /Launch /F %s >>%s >>',
                        $rect,
                        $this->pdfString($ann['target']),
                        $structParentPart,
                    ),
                    default => sprintf(
                        '<< /Type /Annot /Subtype /Link /Rect %s '
                        .'/Border [0 0 0] /Dest %s%s >>',
                        $rect,
                        $this->pdfNameString($ann['target']),
                        $structParentPart,
                    ),
                };
                $linkAnnotId = $writer->addObject($body);
                $annotIds[] = $linkAnnotId;

                // Tagged PDF — register /Link struct element referencing
                // this annotation via /OBJR; reserve a ParentTree entry
                // under the reserved StructParent key.
                if ($this->tagged) {
                    $structIdx = count($this->structElements);
                    $this->structElements[] = [
                        'type' => 'Link',
                        'mcid' => null,
                        'page' => $page,
                        'objr' => $linkAnnotId,
                        'parent' => null,
                        'children' => [],
                    ];
                    $this->structParentLinkKeys[$structIdx] = $structParentKey;
                }
            }
            // Markup annotations (Text/Highlight/Underline/StrikeOut/FreeText).
            foreach ($page->markupAnnotations() as $ann) {
                $annotIds[] = $writer->addObject($this->buildMarkupAnnotation($ann));
            }
            // AcroForm widgets — emit widget annotations + optional /AA
            // JavaScript actions + collect field object IDs.
            foreach ($page->formFields() as $field) {
                $tooltipPart = $field['tooltip'] !== null
                    ? ' /TU '.$this->pdfString($field['tooltip'])
                    : '';
                $namePart = '/T '.$this->pdfString($field['name']);

                if ($field['type'] === 'radio-group') {
                    [$parentId, $childIds] = $this->emitRadioGroupFields(
                        $writer, $pageIds[$i], $field, $namePart, $tooltipPart,
                    );
                    foreach ($childIds as $cid) {
                        $annotIds[] = $cid;
                    }
                    $this->collectedFormFieldIds[] = $parentId;

                    continue;
                }

                // Emit JavaScript action objects + /AA dict ref.
                $aaPart = $this->emitFieldActions($writer, $field);

                $body = $this->buildSimpleFieldObject($writer, $field, $pageIds[$i], $namePart, $tooltipPart, $aaPart);
                $fieldId = $writer->addObject($body);
                $annotIds[] = $fieldId;
                $this->collectedFormFieldIds[] = $fieldId;

                // Track fields with calculate scripts.
                if (! empty($field['calculateScript'])) {
                    $this->calculatedFieldIds[] = $fieldId;
                }
            }
            $annotsRef = $annotIds === []
                ? ''
                : ' /Annots ['.implode(' ', array_map(fn ($id) => "$id 0 R", $annotIds)).']';

            // Page transitions + auto-advance.
            $transRef = '';
            $trans = $page->transition();
            if ($trans !== null) {
                $extras = '';
                if ($trans['dimension'] !== null) {
                    $extras .= ' /Dm /'.$trans['dimension'];
                }
                if ($trans['direction'] !== null) {
                    $extras .= ' /Di '.$trans['direction'];
                }
                $transRef = sprintf(
                    ' /Trans << /Type /Trans /S /%s /D %s%s >>',
                    $trans['style'], $this->fmt($trans['duration']), $extras,
                );
            }
            $durRef = '';
            $dur = $page->autoAdvanceDuration();
            if ($dur !== null) {
                $durRef = ' /Dur '.$this->fmt($dur);
            }

            // /StructParents key linking page to /ParentTree entry.
            $structParentsRef = $this->tagged ? " /StructParents $i" : '';
            // Page rotation /Rotate.
            $rotateRef = $page->rotation() !== 0 ? ' /Rotate '.$page->rotation() : '';
            // Optional page boxes (/CropBox /BleedBox /TrimBox /ArtBox).
            $boxRef = '';
            $trimBox = $page->trimBox();
            // PDF/X (ISO 15930) requires a TrimBox or ArtBox on every page;
            // default the TrimBox to the full MediaBox when neither is set.
            if ($this->pdfX !== null && $trimBox === null && $page->artBox() === null) {
                $trimBox = [0.0, 0.0, $page->widthPt(), $page->heightPt()];
            }
            foreach ([
                'CropBox' => $page->cropBox(),
                'BleedBox' => $page->bleedBox(),
                'TrimBox' => $trimBox,
                'ArtBox' => $page->artBox(),
            ] as $name => $box) {
                if ($box !== null) {
                    $boxRef .= sprintf(
                        ' /%s [%s %s %s %s]',
                        $name,
                        $this->fmt($box[0]), $this->fmt($box[1]),
                        $this->fmt($box[2]), $this->fmt($box[3]),
                    );
                }
            }

            // Page-level /AA Additional Actions (open/close JavaScript).
            $aaRef = '';
            $openScript = $page->openActionScript();
            $closeScript = $page->closeActionScript();
            if ($openScript !== null || $closeScript !== null) {
                $aaParts = [];
                if ($openScript !== null) {
                    $aaParts[] = '/O << /Type /Action /S /JavaScript /JS '
                        . $this->pdfString($openScript) . ' >>';
                }
                if ($closeScript !== null) {
                    $aaParts[] = '/C << /Type /Action /S /JavaScript /JS '
                        . $this->pdfString($closeScript) . ' >>';
                }
                $aaRef = ' /AA << ' . implode(' ', $aaParts) . ' >>';
            }

            // /Parent points to the immediate parent (root or intermediate).
            $parentId = $pageTree['parentOf'][$i];
            // Optional /Tabs entry for form field tab navigation.
            $tabsRef = $page->tabOrder() !== null ? ' /Tabs /'.$page->tabOrder() : '';
            $writer->setObject($pageIds[$i], sprintf(
                '<< /Type /Page /Parent %d 0 R /MediaBox [0 0 %s %s] '
                .'/Contents %d 0 R /Resources %s%s%s%s%s%s%s%s%s >>',
                $parentId,
                $this->fmt($page->widthPt()),
                $this->fmt($page->heightPt()),
                $contentId,
                $resourcesDict,
                $annotsRef,
                $transRef,
                $durRef,
                $structParentsRef,
                $rotateRef,
                $boxRef,
                $aaRef,
                $tabsRef,
            ));
        }

        // Emit intermediate /Pages nodes (only when using a balanced tree).
        foreach ($pageTree['intermediates'] as $node) {
            $kidsList = implode(' ', array_map(fn ($id) => "$id 0 R", $node['kids']));
            $writer->setObject($node['id'], sprintf(
                '<< /Type /Pages /Parent %d 0 R /Kids [%s] /Count %d >>',
                $pagesId, $kidsList, $node['count'],
            ));
        }

        // Named destinations — emit /Names tree if any are registered.
        $namesRef = '';
        $namesEntries = []; // parts collected for root /Names dict.
        if ($this->namedDestinations !== []) {
            $entries = [];
            $names = array_keys($this->namedDestinations);
            sort($names, SORT_STRING);
            foreach ($names as $name) {
                $dest = $this->namedDestinations[$name];
                $pageObjId = $pageObjectIdMap[$dest['page']];
                $entries[] = sprintf(
                    '%s [%d 0 R /XYZ %s %s 0]',
                    $this->pdfString($name),
                    $pageObjId,
                    $this->fmt($dest['x']),
                    $this->fmt($dest['y']),
                );
            }
            $destsId = $writer->addObject('<< /Names ['.implode(' ', $entries).'] >>');
            $namesEntries[] = "/Dests $destsId 0 R";
        }

        // Embedded files (attachments) — /EmbeddedFiles in Names tree.
        if ($this->embeddedFiles !== []) {
            $efEntries = [];
            // Sort by name for deterministic output.
            $sortedFiles = $this->embeddedFiles;
            usort($sortedFiles, fn ($a, $b) => strcmp($a['name'], $b['name']));
            foreach ($sortedFiles as $file) {
                // Embedded file stream — bytes + Subtype + Params /Size.
                $fileSize = strlen($file['bytes']);
                $efStreamBody = sprintf(
                    '<< /Type /EmbeddedFile%s /Length %d /Params << /Size %d >> >>',
                    $file['mimeType'] !== null ? ' /Subtype /'.$this->sanitizeMimeName($file['mimeType']) : '',
                    $fileSize,
                    $fileSize,
                );
                $efStreamId = $writer->addObject(sprintf(
                    "%s\nstream\n%s\nendstream",
                    $efStreamBody, $file['bytes'],
                ));
                // Filespec dict.
                $descPart = $file['description'] !== null
                    ? ' /Desc '.$this->pdfString($file['description'])
                    : '';
                $filespecId = $writer->addObject(sprintf(
                    '<< /Type /Filespec /F %s /UF %s%s '
                    .'/EF << /F %d 0 R /UF %d 0 R >> >>',
                    $this->pdfString($file['name']),
                    $this->pdfString($file['name']),
                    $descPart,
                    $efStreamId,
                    $efStreamId,
                ));
                $efEntries[] = $this->pdfString($file['name'])." $filespecId 0 R";
            }
            $efNamesId = $writer->addObject('<< /Names ['.implode(' ', $efEntries).'] >>');
            $namesEntries[] = "/EmbeddedFiles $efNamesId 0 R";
        }

        if ($namesEntries !== []) {
            $namesId = $writer->addObject('<< '.implode(' ', $namesEntries).' >>');
            $namesRef = ' /Names '.$namesId.' 0 R';
        }

        // Outline tree — bookmarks panel.
        $outlinesRef = '';
        if ($this->outlineEntries !== []) {
            $outlinesId = $this->emitOutlineTree($writer, $pageObjectIdMap);
            $outlinesRef = ' /Outlines '.$outlinesId.' 0 R /PageMode /UseOutlines';
        }

        // 5. Pages tree (after all pages emitted — all IDs are known).
        // Root /Kids — either list of pages (flat) or list of intermediate
        // /Pages nodes (balanced).
        $rootKidsRefs = implode(' ', array_map(fn ($id) => "$id 0 R", $pageTree['rootKids']));
        $writer->setObject($pagesId, sprintf(
            '<< /Type /Pages /Kids [%s] /Count %d >>',
            $rootKidsRefs, count($pageIds),
        ));

        // AcroForm reference in Catalog.
        // /CO — calc field order.
        // /DA + /DR — default appearance string + font resources.
        $acroFormRef = '';
        if ($this->collectedFormFieldIds !== []) {
            $fieldsArray = implode(' ', array_map(fn ($id) => "$id 0 R", $this->collectedFormFieldIds));
            $coRef = '';
            if ($this->calculatedFieldIds !== []) {
                $coArray = implode(' ', array_map(fn ($id) => "$id 0 R", $this->calculatedFieldIds));
                $coRef = " /CO [$coArray]";
            }
            // Default appearance — Helvetica 11pt black. Reuses the
            // appearance font ID if already emitted by appearance-stream
            // builders to avoid duplicate Helvetica objects.
            $defaultFontId = $this->ensureAppearanceFont($writer);
            $daPart = ' /DA (/Helv 11 Tf 0 g)';
            $drPart = sprintf(' /DR << /Font << /Helv %d 0 R >> >>', $defaultFontId);
            // /SigFlags 3 (SignaturesExist | AppendOnly) when signing.
            $sigFlagsPart = ($this->signatureConfig !== null && $this->signatureFieldLinked)
                ? ' /SigFlags 3'
                : '';
            $acroFormId = $writer->addObject(sprintf(
                '<< /Fields [%s] /NeedAppearances true%s%s%s%s >>',
                $fieldsArray, $coRef, $daPart, $drPart, $sigFlagsPart,
            ));
            $acroFormRef = " /AcroForm $acroFormId 0 R";
        }

        // Emit signature dictionary body + hook writer for post-emit
        // patching. Validates that at least one signature widget received /V.
        if ($this->signatureConfig !== null) {
            if (! $this->signatureFieldLinked) {
                throw new \LogicException(
                    'Document::sign() requires at least one form field of type "signature"',
                );
            }
            $cfg = $this->signatureConfig;
            $sigDictBody = $this->buildSignatureDictBody($cfg);
            $writer->setObject($this->signatureDictId, $sigDictBody);
            $writer->setSignature($cfg, $this->signatureDictId);
        }

        // Tagged PDF — StructTreeRoot + StructElem children + MarkInfo dict.
        $taggedRef = '';
        if ($this->tagged && $this->structElements !== []) {
            $structRootId = $writer->reserveObject();
            // Two passes: reserve an object id per element first, so /P
            // (parent) and /K (children) can cross-reference; then emit.
            $elemIds = [];
            foreach ($this->structElements as $idx => $elem) {
                if (($pageObjectIdMap[$elem['page']] ?? null) !== null) {
                    $elemIds[$idx] = $writer->reserveObject();
                }
            }

            $childIds = [];
            // MCID-carrying leaves per page for /ParentTree — the page
            // entry must be an array indexed BY MCID, so containers
            // (mcid = null) are excluded and leaves sorted by mcid.
            $structLeavesPerPage = [];
            foreach ($this->structElements as $idx => $elem) {
                $pageId = $pageObjectIdMap[$elem['page']] ?? null;
                if ($pageId === null) {
                    continue;
                }
                $elemId = $elemIds[$idx];
                $parentRef = $elem['parent'] !== null && isset($elemIds[$elem['parent']])
                    ? $elemIds[$elem['parent']]
                    : $structRootId;
                $altPart = '';
                if (! empty($elem['altText'])) {
                    $altPart = ' /Alt '.$this->pdfString((string) $elem['altText']);
                }
                if (! empty($elem['objr'])) {
                    $kPart = sprintf('<< /Type /OBJR /Obj %d 0 R >>', $elem['objr']);
                } elseif ($elem['mcid'] === null) {
                    $kids = array_map(
                        fn (int $childIdx) => sprintf('%d 0 R', $elemIds[$childIdx]),
                        array_values(array_filter($elem['children'], fn (int $c) => isset($elemIds[$c]))),
                    );
                    $kPart = '['.implode(' ', $kids).']';
                } else {
                    $kPart = (string) $elem['mcid'];
                }
                $writer->setObject($elemId, sprintf(
                    '<< /Type /StructElem /S /%s /P %d 0 R /Pg %d 0 R /K %s%s >>',
                    $elem['type'], $parentRef, $pageId, $kPart, $altPart,
                ));
                if ($elem['parent'] === null) {
                    $childIds[] = $elemId;
                }

                if ($elem['mcid'] !== null && empty($elem['objr'])) {
                    $pageIdx = array_search($elem['page'], $this->pages, true);
                    if ($pageIdx !== false) {
                        $structLeavesPerPage[$pageIdx] ??= [];
                        $structLeavesPerPage[$pageIdx][$elem['mcid']] = $elemId;
                    }
                }
            }
            $kidsArray = '['.implode(' ', array_map(fn ($id) => "$id 0 R", $childIds)).']';

            // Emit /ParentTree (number tree) — per-page arrays indexed by
            // MCID, mapping each marked-content sequence to its element.
            ksort($structLeavesPerPage);
            $parentTreeNums = [];
            foreach ($structLeavesPerPage as $pageIdx => $byMcid) {
                ksort($byMcid);
                $refs = implode(' ', array_map(fn ($id) => "$id 0 R", $byMcid));
                $parentTreeNums[] = "$pageIdx [$refs]";
            }
            // Per-Link /StructParent entries — map key to single struct
            // elem reference (NOT an array; OBJR convention).
            $maxParentKey = count($this->pages);
            foreach ($this->structParentLinkKeys as $structIdx => $key) {
                $elemId = $elemIds[$structIdx] ?? null;
                if ($elemId !== null) {
                    $parentTreeNums[] = "$key $elemId 0 R";
                    if ($key + 1 > $maxParentKey) {
                        $maxParentKey = $key + 1;
                    }
                }
            }
            $parentTreeId = $writer->addObject(
                '<< /Nums ['.implode(' ', $parentTreeNums).'] >>',
            );

            // Optional /RoleMap dict.
            $roleMapRef = '';
            if ($this->structRoleMap !== []) {
                $entries = [];
                foreach ($this->structRoleMap as $custom => $standard) {
                    $entries[] = '/'.$custom.' /'.$standard;
                }
                $roleMapRef = ' /RoleMap << '.implode(' ', $entries).' >>';
            }

            $writer->setObject($structRootId, sprintf(
                '<< /Type /StructTreeRoot /K %s /ParentTree %d 0 R '
                .'/ParentTreeNextKey %d%s >>',
                $kidsArray,
                $parentTreeId,
                $maxParentKey,
                $roleMapRef,
            ));
            $taggedRef = sprintf(
                ' /MarkInfo << /Marked true >> /StructTreeRoot %d 0 R',
                $structRootId,
            );
        }

        // PDF/A — Metadata stream + OutputIntent + /Lang.
        $pdfARef = '';
        if ($this->pdfA !== null) {
            $xmp = $this->pdfA->xmpMetadata();
            // Metadata stream — not filtered, not encrypted.
            $metadataId = $writer->addObject(sprintf(
                "<< /Type /Metadata /Subtype /XML /Length %d >>\nstream\n%s\nendstream",
                strlen($xmp),
                $xmp,
            ));
            // Embedded ICC profile — Flate-compressed stream; /N derives
            // from the profile's colour space (RGB → 3, CMYK → 4, GRAY → 1).
            $iccBytes = $this->pdfA->iccProfileBytes();
            $iccCompressed = (string) gzcompress($iccBytes, 6);
            $iccId = $writer->addObject(sprintf(
                "<< /N %d /Length %d /Filter /FlateDecode >>\nstream\n%s\nendstream",
                self::iccComponents($iccBytes),
                strlen($iccCompressed),
                $iccCompressed,
            ));
            $outputIntentBody = sprintf(
                '<< /Type /OutputIntent /S /GTS_PDFA1 '
                .'/OutputConditionIdentifier %s '
                .'/Info %s '
                .'/DestOutputProfile %d 0 R >>',
                $this->pdfString($this->pdfA->iccProfileName),
                $this->pdfString($this->pdfA->iccProfileName),
                $iccId,
            );
            $outputIntentId = $writer->addObject($outputIntentBody);
            $pdfARef = sprintf(
                ' /Metadata %d 0 R /OutputIntents [%d 0 R] /Lang %s',
                $metadataId,
                $outputIntentId,
                $this->pdfString($this->pdfA->lang),
            );
        }

        // PDF/X — Metadata stream + OutputIntent with /S /GTS_PDFX.
        if ($this->pdfX !== null) {
            $xmp = $this->pdfX->xmpMetadata();
            $metadataId = $writer->addObject(sprintf(
                "<< /Type /Metadata /Subtype /XML /Length %d >>\nstream\n%s\nendstream",
                strlen($xmp), $xmp,
            ));
            $iccBytes = $this->pdfX->iccProfileBytes();
            $iccCompressed = (string) gzcompress($iccBytes, 6);
            $iccId = $writer->addObject(sprintf(
                "<< /N %d /Length %d /Filter /FlateDecode >>\nstream\n%s\nendstream",
                self::iccComponents($iccBytes),
                strlen($iccCompressed), $iccCompressed,
            ));
            $outputIntentBody = sprintf(
                '<< /Type /OutputIntent /S /GTS_PDFX '
                .'/OutputConditionIdentifier %s '
                .'/OutputCondition %s '
                .'/RegistryName %s '
                .'/Info %s '
                .'/DestOutputProfile %d 0 R >>',
                $this->pdfString($this->pdfX->outputConditionIdentifier),
                $this->pdfString($this->pdfX->outputCondition),
                $this->pdfString($this->pdfX->registryName),
                $this->pdfString($this->pdfX->iccProfileName),
                $iccId,
            );
            $outputIntentId = $writer->addObject($outputIntentBody);
            $pdfARef = sprintf(
                ' /Metadata %d 0 R /OutputIntents [%d 0 R]',
                $metadataId, $outputIntentId,
            );
        }

        // 6. Catalog.
        // Optional /OpenAction, /PageMode, /PageLayout.
        $openActionRef = '';
        if ($this->openAction !== null) {
            $pIdx = max(0, $this->openAction['page'] - 1);
            $pageId = $pageIds[$pIdx] ?? $pageIds[0];
            $mode = $this->openAction['mode'];
            $action = match ($mode) {
                'fit-page' => sprintf('[%d 0 R /Fit]', $pageId),
                'fit-width' => sprintf('[%d 0 R /FitH null]', $pageId),
                'actual-size' => sprintf('[%d 0 R /XYZ null null 1]', $pageId),
                'xyz' => sprintf(
                    '[%d 0 R /XYZ %s %s %s]',
                    $pageId,
                    $this->openAction['x'] !== null ? $this->fmt($this->openAction['x']) : 'null',
                    $this->openAction['y'] !== null ? $this->fmt($this->openAction['y']) : 'null',
                    $this->openAction['zoom'] !== null ? $this->fmt($this->openAction['zoom']) : 'null',
                ),
                default => sprintf('[%d 0 R /Fit]', $pageId),
            };
            $openActionRef = " /OpenAction $action";
        }
        $pageModeRef = '';
        if ($this->pageMode !== null) {
            $pageModeRef = ' /PageMode /'.match ($this->pageMode) {
                'use-outlines' => 'UseOutlines',
                'use-thumbs' => 'UseThumbs',
                'use-oc' => 'UseOC',
                'full-screen' => 'FullScreen',
                default => 'UseNone',
            };
        }
        $pageLayoutRef = '';
        if ($this->pageLayout !== null) {
            $pageLayoutRef = ' /PageLayout /'.match ($this->pageLayout) {
                'one-column' => 'OneColumn',
                'two-column-left' => 'TwoColumnLeft',
                'two-column-right' => 'TwoColumnRight',
                'two-page-left' => 'TwoPageLeft',
                'two-page-right' => 'TwoPageRight',
                default => 'SinglePage',
            };
        }

        // /PageLabels number tree.
        $pageLabelsRef = '';
        if ($this->pageLabelRanges !== []) {
            $styleMap = [
                'decimal' => 'D',
                'upper-roman' => 'R',
                'lower-roman' => 'r',
                'upper-alpha' => 'A',
                'lower-alpha' => 'a',
            ];
            $numsEntries = [];
            foreach ($this->pageLabelRanges as $range) {
                $parts = [];
                if (isset($range['style']) && isset($styleMap[$range['style']])) {
                    $parts[] = '/S /'.$styleMap[$range['style']];
                }
                if (isset($range['prefix']) && $range['prefix'] !== '') {
                    $parts[] = '/P '.$this->pdfString($range['prefix']);
                }
                if (isset($range['firstNumber'])) {
                    $parts[] = '/St '.$range['firstNumber'];
                }
                $dict = '<< '.implode(' ', $parts).' >>';
                $numsEntries[] = $range['startPage'].' '.$dict;
            }
            $pageLabelsRef = ' /PageLabels << /Nums ['.implode(' ', $numsEntries).'] >>';
        }

        // /ViewerPreferences dict.
        $viewerPrefsRef = '';
        if ($this->viewerPreferences !== []) {
            $entries = [];
            $boolKeys = [
                'hideToolbar' => 'HideToolbar', 'hideMenubar' => 'HideMenubar',
                'hideWindowUI' => 'HideWindowUI', 'fitWindow' => 'FitWindow',
                'centerWindow' => 'CenterWindow', 'displayDocTitle' => 'DisplayDocTitle',
            ];
            foreach ($boolKeys as $k => $name) {
                if (isset($this->viewerPreferences[$k])) {
                    $entries[] = "/$name ".($this->viewerPreferences[$k] ? 'true' : 'false');
                }
            }
            if (isset($this->viewerPreferences['direction'])) {
                $entries[] = '/Direction /'.$this->viewerPreferences['direction'];
            }
            if (isset($this->viewerPreferences['printScaling'])) {
                $entries[] = '/PrintScaling /'.$this->viewerPreferences['printScaling'];
            }
            if (isset($this->viewerPreferences['duplex'])) {
                $entries[] = '/Duplex /'.$this->viewerPreferences['duplex'];
            }
            if ($entries !== []) {
                $viewerPrefsRef = ' /ViewerPreferences << '.implode(' ', $entries).' >>';
            }
        }

        // /Lang entry in Catalog (PDF/UA requirement).
        $langRef = '';
        if ($this->lang !== null && $this->lang !== '') {
            // Avoid double-emission if PDF/A mode already injects /Lang.
            if ($this->pdfA === null) {
                $langRef = ' /Lang '.$this->pdfString($this->lang);
            }
        }

        // Document-level /AA additional actions (Will*/Did*).
        $documentAARef = '';
        if ($this->documentActions !== []) {
            $aaParts = [];
            foreach ($this->documentActions as $event => $script) {
                $aaParts[] = sprintf(
                    '/%s << /Type /Action /S /JavaScript /JS %s >>',
                    $event, $this->pdfString($script),
                );
            }
            $documentAARef = ' /AA << ' . implode(' ', $aaParts) . ' >>';
        }

        // /OCProperties for Optional Content Groups (layers).
        $ocPropertiesRef = '';
        if ($this->layers !== []) {
            $ocgArray = [];
            $onArray = [];
            $offArray = [];
            $orderArray = [];
            foreach ($this->layers as $layer) {
                $id = $layerObjectIds[$layer];
                $ocgArray[] = "$id 0 R";
                $orderArray[] = "$id 0 R";
                if ($layer->defaultVisible) {
                    $onArray[] = "$id 0 R";
                } else {
                    $offArray[] = "$id 0 R";
                }
            }
            $onPart = $onArray === [] ? '' : ' /ON ['.implode(' ', $onArray).']';
            $offPart = $offArray === [] ? '' : ' /OFF ['.implode(' ', $offArray).']';
            $ocPropertiesRef = sprintf(
                ' /OCProperties << /OCGs [%s] /D << /Order [%s]%s%s >> >>',
                implode(' ', $ocgArray),
                implode(' ', $orderArray),
                $onPart,
                $offPart,
            );
        }

        $writer->setObject($catalogId, "<< /Type /Catalog /Pages $pagesId 0 R$namesRef$outlinesRef$acroFormRef$pdfARef$taggedRef$openActionRef$pageModeRef$pageLayoutRef$pageLabelsRef$viewerPrefsRef$langRef$ocPropertiesRef$documentAARef >>");

        $writer->setRoot($catalogId);

        // /Info dictionary is always emitted with default Producer +
        // CreationDate (most readers expect Info dict in Properties dialog).
        $meta = $this->metadata + [
            'Producer' => 'dskripchenko/php-pdf',
            'CreationDate' => $this->formatPdfDate(new \DateTimeImmutable),
        ];
        $entries = [];
        foreach ($meta as $key => $value) {
            $entries[] = '/'.$key.' '.$this->pdfString((string) $value);
        }
        // PDF/X requires /GTS_PDFXVersion, /ModDate and /Trapped in /Info.
        if ($this->pdfX !== null) {
            $entries[] = '/GTS_PDFXVersion '.$this->pdfString($this->pdfX->variant);
            if (! isset($this->metadata['ModDate'])) {
                $entries[] = '/ModDate '.$this->pdfString((string) $meta['CreationDate']);
            }
            $entries[] = '/Trapped /'.$this->pdfX->trapped;
            // Title from PdfXConfig if not set in metadata.
            if (! isset($this->metadata['Title']) && $this->pdfX->title !== '') {
                $entries[] = '/Title '.$this->pdfString($this->pdfX->title);
            }
            if (! isset($this->metadata['Author']) && $this->pdfX->author !== '') {
                $entries[] = '/Author '.$this->pdfString($this->pdfX->author);
            }
        }
        $infoId = $writer->addObject('<< '.implode(' ', $entries).' >>');
        $writer->setInfo($infoId);

        // Emit /Encrypt object and hook encryption into the writer.
        if ($this->encryption !== null) {
            $enc = $this->encryption;
            $oHex = bin2hex($enc->oValue);
            $uHex = bin2hex($enc->uValue);
            if ($enc->algorithm === EncryptionAlgorithm::Aes_256
                || $enc->algorithm === EncryptionAlgorithm::Aes_256_R6) {
                // V5 R5 (Adobe Supplement) or V5 R6 (PDF 2.0) + AESV3 Crypt Filter.
                $revision = $enc->algorithm === EncryptionAlgorithm::Aes_256_R6 ? 6 : 5;
                $oeHex = bin2hex($enc->oeValue);
                $ueHex = bin2hex($enc->ueValue);
                $permsHex = bin2hex($enc->permsValue);
                $encryptBody = sprintf(
                    '<< /Filter /Standard /V 5 /R %d /Length 256 '
                    .'/CF << /StdCF << /CFM /AESV3 /Length 32 /AuthEvent /DocOpen >> >> '
                    .'/StmF /StdCF /StrF /StdCF '
                    .'/O <%s> /U <%s> /OE <%s> /UE <%s> /Perms <%s> /P %d >>',
                    $revision, $oHex, $uHex, $oeHex, $ueHex, $permsHex, $enc->permissions,
                );
            } elseif ($enc->algorithm === EncryptionAlgorithm::Aes_128) {
                // V4 R4 + AESV2 Crypt Filter.
                $encryptBody = sprintf(
                    '<< /Filter /Standard /V 4 /R 4 /Length 128 '
                    .'/CF << /StdCF << /CFM /AESV2 /Length 16 /AuthEvent /DocOpen >> >> '
                    .'/StmF /StdCF /StrF /StdCF '
                    .'/O <%s> /U <%s> /P %d >>',
                    $oHex,
                    $uHex,
                    $enc->permissions,
                );
            } else {
                $encryptBody = sprintf(
                    '<< /Filter /Standard /V 2 /R 3 /Length 128 /O <%s> /U <%s> /P %d >>',
                    $oHex,
                    $uHex,
                    $enc->permissions,
                );
            }
            $encryptId = $writer->addObject($encryptBody);
            $writer->setEncryption($enc, $encryptId);
        }

        return $writer;
    }

    /**
     * Serialize the document to a file. Returns the number of bytes written.
     * Uses streaming Writer::toStream to avoid a full-document copy in
     * string memory.
     */
    public function toFile(string $path): int
    {
        $fp = fopen($path, 'wb');
        if ($fp === false) {
            throw new \RuntimeException('Failed to open ' . $path . ' for writing');
        }
        try {
            return $this->toStream($fp);
        } finally {
            fclose($fp);
        }
    }

    /**
     * Streaming PDF output to an external stream resource.
     *
     * Use cases: large PDFs (thousands of pages), HTTP responses without
     * holding the full document in memory, file output without double
     * buffering.
     *
     * Currently emits the final document to the stream — objects are still
     * accumulated in memory but the final assembly is streamed. Full
     * per-object streaming requires a deeper API rewrite.
     *
     * @param  resource  $stream
     * @return int  bytes written
     */
    public function toStream($stream): int
    {
        $writer = $this->buildWriter();

        return $writer->toStream($stream);
    }

    /**
     * Emit the outline tree (bookmarks panel). Takes the flat
     * $outlineEntries and builds a nested structure by level via a stack:
     *  - Level 1 → child of Outlines root.
     *  - Level N+1 → child of last level-N entry.
     *  - Skip-levels (1→3) treated as 1→2 (no virtual filler).
     *
     * @param  \SplObjectStorage<Page, int>  $pageObjectIdMap
     */
    private function emitOutlineTree(Writer $writer, \SplObjectStorage $pageObjectIdMap): int
    {
        $count = count($this->outlineEntries);

        // Step 1: reserve IDs.
        $entryIds = [];
        for ($i = 0; $i < $count; $i++) {
            $entryIds[$i] = $writer->reserveObject();
        }
        $outlinesId = $writer->reserveObject();

        // Step 2: compute hierarchy.
        // parents[i] = index of parent entry, or null for top-level.
        // children[parentIdx] = list<childIdx>.
        $parents = [];
        $children = [];
        $stack = []; // stack of [entryIdx, level]
        foreach ($this->outlineEntries as $i => $entry) {
            $level = $entry['level'];
            while ($stack !== [] && end($stack)[1] >= $level) {
                array_pop($stack);
            }
            $parentIdx = $stack === [] ? null : end($stack)[0];
            $parents[$i] = $parentIdx;
            if ($parentIdx === null) {
                $children[-1] = $children[-1] ?? [];
                $children[-1][] = $i;
            } else {
                $children[$parentIdx] = $children[$parentIdx] ?? [];
                $children[$parentIdx][] = $i;
            }
            $stack[] = [$i, $level];
        }
        $topLevel = $children[-1] ?? [];

        // Step 3: emit each entry.
        foreach ($this->outlineEntries as $i => $entry) {
            $parentIdx = $parents[$i];
            $parentRef = $parentIdx === null
                ? $outlinesId.' 0 R'
                : $entryIds[$parentIdx].' 0 R';

            // Find siblings (same parent's children list).
            $siblings = $parentIdx === null ? $topLevel : ($children[$parentIdx] ?? []);
            $position = array_search($i, $siblings, strict: true);
            $prevRef = ($position !== false && $position > 0)
                ? $entryIds[$siblings[$position - 1]].' 0 R'
                : null;
            $nextRef = ($position !== false && $position < count($siblings) - 1)
                ? $entryIds[$siblings[$position + 1]].' 0 R'
                : null;

            // First/Last child.
            $myChildren = $children[$i] ?? [];
            $firstChildRef = $myChildren !== [] ? $entryIds[$myChildren[0]].' 0 R' : null;
            $lastChildRef = $myChildren !== [] ? $entryIds[$myChildren[count($myChildren) - 1]].' 0 R' : null;

            // Count of descendants (positive — open by default).
            $countDesc = $this->countDescendants($i, $children);

            $pageObjId = $pageObjectIdMap[$entry['page']];
            $dest = sprintf('[%d 0 R /XYZ %s %s 0]',
                $pageObjId, $this->fmt($entry['x']), $this->fmt($entry['y']));

            $parts = [
                '/Title '.$this->pdfString($entry['title']),
                '/Parent '.$parentRef,
                '/Dest '.$dest,
            ];
            if ($prevRef !== null) {
                $parts[] = '/Prev '.$prevRef;
            }
            if ($nextRef !== null) {
                $parts[] = '/Next '.$nextRef;
            }
            if ($firstChildRef !== null) {
                $parts[] = '/First '.$firstChildRef;
                $parts[] = '/Last '.$lastChildRef;
                $parts[] = '/Count '.$countDesc;
            }
            // Optional /C (color RGB) + /F (style flags).
            if (! empty($entry['color'])) {
                [$r, $g, $b] = $this->hexToRgb01((string) $entry['color']);
                $parts[] = sprintf('/C [%s %s %s]',
                    $this->fmt($r), $this->fmt($g), $this->fmt($b));
            }
            $flags = 0;
            if (! empty($entry['italic'])) {
                $flags |= 1;
            }
            if (! empty($entry['bold'])) {
                $flags |= 2;
            }
            if ($flags !== 0) {
                $parts[] = '/F '.$flags;
            }
            $writer->setObject($entryIds[$i], '<< '.implode(' ', $parts).' >>');
        }

        // Step 4: emit Outlines root.
        $topCount = count($topLevel);
        $totalDesc = 0;
        foreach ($topLevel as $idx) {
            $totalDesc += 1 + $this->countDescendants($idx, $children);
        }
        $rootParts = ['/Type /Outlines'];
        if ($topLevel !== []) {
            $rootParts[] = '/First '.$entryIds[$topLevel[0]].' 0 R';
            $rootParts[] = '/Last '.$entryIds[$topLevel[count($topLevel) - 1]].' 0 R';
            $rootParts[] = '/Count '.$totalDesc;
        }
        $writer->setObject($outlinesId, '<< '.implode(' ', $rootParts).' >>');

        return $outlinesId;
    }

    /**
     * Recursive count of descendants for the outline entry /Count field.
     *
     * @param  array<int, list<int>>  $children
     */
    private function countDescendants(int $idx, array $children): int
    {
        $myChildren = $children[$idx] ?? [];
        $count = count($myChildren);
        foreach ($myChildren as $childIdx) {
            $count += $this->countDescendants($childIdx, $children);
        }

        return $count;
    }

    /**
     * Emit JavaScript action objects and return the /AA dict string.
     * Returns an empty string if no actions are defined.
     *
     * @param  array<string, mixed>  $field
     */
    private function emitFieldActions(Writer $writer, array $field): string
    {
        $entries = [];
        $scriptMap = [
            'K' => 'keystrokeScript',
            'V' => 'validateScript',
            'C' => 'calculateScript',
            'F' => 'formatScript',
        ];
        foreach ($scriptMap as $entry => $key) {
            $script = $field[$key] ?? null;
            if ($script === null) {
                continue;
            }
            $actionBody = sprintf(
                '<< /Type /Action /S /JavaScript /JS %s >>',
                $this->pdfString($script),
            );
            $actionId = $writer->addObject($actionBody);
            $entries[] = "/$entry $actionId 0 R";
        }
        if ($entries === []) {
            return '';
        }

        return ' /AA << '.implode(' ', $entries).' >>';
    }

    /**
     * Build the object body for a single-widget AcroForm field.
     *
     * @param  array<string, mixed>  $field
     */
    private function buildSimpleFieldObject(Writer $writer, array $field, int $pageId, string $namePart, string $tooltipPart, string $aaPart = ''): string
    {
        $rect = sprintf(
            '[%s %s %s %s]',
            $this->fmt($field['x']), $this->fmt($field['y']),
            $this->fmt($field['x'] + $field['w']),
            $this->fmt($field['y'] + $field['h']),
        );
        $flags = 0;
        if ($field['readOnly']) {
            $flags |= 1;
        }
        if ($field['required']) {
            $flags |= 2;
        }
        $type = $field['type'];
        // Type-specific flags (high-numbered bits per ISO 32000-1 Table 228).
        if ($type === 'text-multiline') {
            $flags |= 4096; // Multiline bit 13.
        }
        if ($type === 'password') {
            $flags |= 8192; // Password bit 14.
        }
        if ($type === 'combo') {
            $flags |= 131072; // Combo bit 18.
        }

        if (in_array($type, ['text', 'text-multiline', 'password'], true)) {
            $valuePart = $field['defaultValue'] !== ''
                ? ' /V '.$this->pdfString($field['defaultValue'])
                    .' /DV '.$this->pdfString($field['defaultValue'])
                : '';
            // Appearance stream rendering the field value.
            $apId = $this->buildTextFieldAppearance(
                $writer,
                (float) $field['w'], (float) $field['h'],
                $type === 'password' ? str_repeat('*', mb_strlen((string) $field['defaultValue'], 'UTF-8')) : (string) $field['defaultValue'],
                multiline: $type === 'text-multiline',
            );
            $apRef = sprintf(' /AP << /N %d 0 R >>', $apId);

            return sprintf(
                '<< /Type /Annot /Subtype /Widget /FT /Tx /Rect %s '
                .'%s%s%s /Ff %d /P %d 0 R%s%s >>',
                $rect, $namePart, $valuePart, $tooltipPart, $flags, $pageId, $apRef, $aaPart,
            );
        }
        if ($type === 'checkbox') {
            $isChecked = strcasecmp($field['defaultValue'], 'on') === 0
                || strcasecmp($field['defaultValue'], 'yes') === 0
                || $field['defaultValue'] === '1';
            $vPart = $isChecked ? ' /V /Yes /DV /Yes' : ' /V /Off /DV /Off';
            // AP dict with both states (Yes + Off).
            $yesId = $this->buildCheckboxAppearance($writer, (float) $field['w'], (float) $field['h'], checked: true);
            $offId = $this->buildCheckboxAppearance($writer, (float) $field['w'], (float) $field['h'], checked: false);
            $apRef = sprintf(' /AP << /N << /Yes %d 0 R /Off %d 0 R >> >>', $yesId, $offId);

            return sprintf(
                '<< /Type /Annot /Subtype /Widget /FT /Btn /Rect %s '
                .'%s%s%s /Ff %d /P %d 0 R /AS %s%s%s >>',
                $rect, $namePart, $vPart, $tooltipPart, $flags, $pageId,
                $isChecked ? '/Yes' : '/Off',
                $apRef, $aaPart,
            );
        }
        if ($type === 'signature') {
            // Signature widget references signature dictionary via /V;
            // only the first signature widget receives /V (subsequent
            // widgets remain unsigned placeholders).
            $vPart = '';
            if ($this->signatureDictId !== null && ! $this->signatureFieldLinked) {
                $vPart = sprintf(' /V %d 0 R', $this->signatureDictId);
                $this->signatureFieldLinked = true;
            }
            // Minimal appearance (blank box with optional caption).
            $apId = $this->buildSignatureAppearance($writer, (float) $field['w'], (float) $field['h']);
            $apRef = sprintf(' /AP << /N %d 0 R >>', $apId);

            return sprintf(
                '<< /Type /Annot /Subtype /Widget /FT /Sig /Rect %s '
                .'%s%s%s /Ff %d /P %d 0 R%s%s >>',
                $rect, $namePart, $vPart, $tooltipPart, $flags, $pageId, $apRef, $aaPart,
            );
        }
        if ($type === 'submit' || $type === 'reset' || $type === 'push') {
            // Button with pushbutton flag (bit 17 = 65536).
            $flags |= 65536;
            $caption = $field['buttonCaption'] ?? ucfirst($type);
            $mkPart = ' /MK << /CA '.$this->pdfString($caption).' >>';

            // /A action dict.
            $actionPart = '';
            if ($type === 'submit') {
                $url = $field['submitUrl'] ?? '';
                $actionPart = ' /A << /Type /Action /S /SubmitForm '
                    .'/F << /FS /URL /F '.$this->pdfString($url).' >> '
                    .'/Flags 0 >>';
            } elseif ($type === 'reset') {
                $actionPart = ' /A << /Type /Action /S /ResetForm >>';
            } elseif ($type === 'push' && ! empty($field['clickScript'])) {
                $actionPart = sprintf(
                    ' /A << /Type /Action /S /JavaScript /JS %s >>',
                    $this->pdfString($field['clickScript']),
                );
            }
            // Appearance — bordered rectangle with caption.
            $apId = $this->buildButtonAppearance(
                $writer, (float) $field['w'], (float) $field['h'], (string) $caption,
            );
            $apRef = sprintf(' /AP << /N %d 0 R >>', $apId);

            return sprintf(
                '<< /Type /Annot /Subtype /Widget /FT /Btn /Rect %s '
                .'%s%s /Ff %d /P %d 0 R%s%s%s%s >>',
                $rect, $namePart, $tooltipPart, $flags, $pageId,
                $mkPart, $actionPart, $apRef, $aaPart,
            );
        }
        if ($type === 'combo' || $type === 'list') {
            $optsArray = '['.implode(' ', array_map(fn ($o) => $this->pdfString($o), $field['options'])).']';
            $valuePart = $field['defaultValue'] !== ''
                ? ' /V '.$this->pdfString($field['defaultValue'])
                    .' /DV '.$this->pdfString($field['defaultValue'])
                : '';
            // Appearance shows the selected value (single line).
            $apId = $this->buildTextFieldAppearance(
                $writer, (float) $field['w'], (float) $field['h'],
                (string) $field['defaultValue'], multiline: false,
            );
            $apRef = sprintf(' /AP << /N %d 0 R >>', $apId);

            return sprintf(
                '<< /Type /Annot /Subtype /Widget /FT /Ch /Rect %s '
                .'%s%s%s /Opt %s /Ff %d /P %d 0 R%s%s >>',
                $rect, $namePart, $valuePart, $tooltipPart, $optsArray, $flags, $pageId, $apRef, $aaPart,
            );
        }
        throw new \LogicException("Unsupported field type: $type");
    }

    /**
     * Emit radio-group parent + N child Widget annotations.
     *
     * @param  array<string, mixed>  $field
     * @return array{0: int, 1: list<int>}  [parentObjId, childObjIds]
     */
    private function emitRadioGroupFields(Writer $writer, int $pageId, array $field, string $namePart, string $tooltipPart): array
    {
        // Reserve parent ID upfront — children reference it via /Parent.
        $parentId = $writer->reserveObject();

        // Flags: Radio bit 16 (val 32768) + NoToggleToOff bit 15 (val 16384).
        $flags = 32768 | 16384;
        if ($field['readOnly']) {
            $flags |= 1;
        }
        if ($field['required']) {
            $flags |= 2;
        }

        $childIds = [];
        foreach ($field['radioWidgets'] as $idx => $widget) {
            $optionLabel = $field['options'][$idx];
            $isChecked = $optionLabel === $field['defaultValue'];
            $rect = sprintf(
                '[%s %s %s %s]',
                $this->fmt($widget['x']), $this->fmt($widget['y']),
                $this->fmt($widget['x'] + $widget['w']),
                $this->fmt($widget['y'] + $widget['h']),
            );
            // Child widget — pure annotation, /T inherited from parent.
            // /AS = export value (escaped as a name); /Off if not selected.
            $exportName = '/'.preg_replace('@[^A-Za-z0-9_-]@', '_', $optionLabel);
            // Appearance — selected (filled circle) + Off (empty).
            $onId = $this->buildRadioAppearance($writer, (float) $widget['w'], (float) $widget['h'], selected: true);
            $offId = $this->buildRadioAppearance($writer, (float) $widget['w'], (float) $widget['h'], selected: false);
            $exportKey = ltrim($exportName, '/');
            $apRef = sprintf(' /AP << /N << /%s %d 0 R /Off %d 0 R >> >>', $exportKey, $onId, $offId);
            $body = sprintf(
                '<< /Type /Annot /Subtype /Widget /Rect %s '
                .'/Parent %d 0 R /AS %s%s >>',
                $rect, $parentId, $isChecked ? $exportName : '/Off', $apRef,
            );
            $childIds[] = $writer->addObject($body);
        }

        $kidsArray = '['.implode(' ', array_map(fn ($id) => "$id 0 R", $childIds)).']';
        $vPart = $field['defaultValue'] !== ''
            ? ' /V /'.preg_replace('@[^A-Za-z0-9_-]@', '_', $field['defaultValue'])
                .' /DV /'.preg_replace('@[^A-Za-z0-9_-]@', '_', $field['defaultValue'])
            : '';
        $parentBody = sprintf(
            '<< /FT /Btn %s%s /Ff %d /Kids %s >>',
            $namePart, $vPart.$tooltipPart, $flags, $kidsArray,
        );
        $writer->setObject($parentId, $parentBody);

        return [$parentId, $childIds];
    }

    /**
     * Parse hex #rrggbb / #rgb → list<float> in [0..1].
     *
     * @return array{0: float, 1: float, 2: float}
     */
    private function hexToRgb01(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return [0, 0, 0];
        }

        return [
            hexdec(substr($hex, 0, 2)) / 255,
            hexdec(substr($hex, 2, 2)) / 255,
            hexdec(substr($hex, 4, 2)) / 255,
        ];
    }

    /**
     * PDF name objects only allow specific chars. Mime types containing
     * '/' must be converted into hash-escaped form (#2F).
     */
    private function sanitizeMimeName(string $mime): string
    {
        $out = '';
        for ($i = 0; $i < strlen($mime); $i++) {
            $c = $mime[$i];
            if (preg_match('@[A-Za-z0-9]@', $c)) {
                $out .= $c;
            } else {
                $out .= '#'.sprintf('%02X', ord($c));
            }
        }

        return $out;
    }

    /**
     * Escape a string for a PDF literal: wrap in `()`, escape `(`, `)`, `\`.
     * Used for URIs and name values in the Names tree.
     */
    private function pdfString(string $s): string
    {
        return '('.strtr($s, ['\\' => '\\\\', '(' => '\\(', ')' => '\\)']).')';
    }

    /**
     * Lazy-init Helvetica font object for appearance streams.
     */
    private function ensureAppearanceFont(Writer $writer): int
    {
        if ($this->appearanceFontId === null) {
            $this->appearanceFontId = $writer->addObject(
                '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica '
                .'/Encoding /WinAnsiEncoding >>',
            );
        }

        return $this->appearanceFontId;
    }

    /**
     * Lazy-init ZapfDingbats font for checkbox/radio glyphs.
     */
    private function ensureAppearanceZapfFont(Writer $writer): int
    {
        if ($this->appearanceZapfId === null) {
            $this->appearanceZapfId = $writer->addObject(
                '<< /Type /Font /Subtype /Type1 /BaseFont /ZapfDingbats >>',
            );
        }

        return $this->appearanceZapfId;
    }

    /**
     * Emit a Form XObject as an appearance stream.
     */
    private function emitAppearanceStream(Writer $writer, float $w, float $h, string $content, string $resources): int
    {
        $bbox = sprintf('[0 0 %s %s]', $this->fmt($w), $this->fmt($h));
        if ($this->compressStreams) {
            $compressed = (string) gzcompress($content, 6);
            $body = sprintf(
                "<< /Type /XObject /Subtype /Form /BBox %s /Resources %s /Length %d /Filter /FlateDecode >>\nstream\n%s\nendstream",
                $bbox, $resources, strlen($compressed), $compressed,
            );
        } else {
            $body = sprintf(
                "<< /Type /XObject /Subtype /Form /BBox %s /Resources %s /Length %d >>\nstream\n%s\nendstream",
                $bbox, $resources, strlen($content), $content,
            );
        }

        return $writer->addObject($body);
    }

    /**
     * Build the text-field appearance Form XObject.
     */
    private function buildTextFieldAppearance(Writer $writer, float $w, float $h, string $text, bool $multiline): int
    {
        $fontId = $this->ensureAppearanceFont($writer);
        $resources = sprintf('<< /Font << /Helv %d 0 R >> >>', $fontId);

        $fontSize = 11.0;
        // Vertical center for single-line; top-aligned for multiline.
        $padding = 2.0;
        $textY = $multiline
            ? ($h - $fontSize - $padding)
            : (($h - $fontSize) / 2.0 + 1.0);
        $textPdf = $text === ''
            ? ''
            : sprintf("BT\n/Helv %s Tf\n0 g\n%s %s Td\n%s Tj\nET\n",
                $this->fmt($fontSize),
                $this->fmt($padding), $this->fmt($textY),
                $this->pdfString($text),
            );
        // q .. Q wrap + clip to bbox.
        $content = sprintf(
            "q\n0 0 %s %s re W n\n%sQ\n",
            $this->fmt($w), $this->fmt($h), $textPdf,
        );

        return $this->emitAppearanceStream($writer, $w, $h, $content, $resources);
    }

    /**
     * Build the checkbox appearance Form XObject — uses the ZapfDingbats
     * check-mark glyph for the checked state.
     */
    private function buildCheckboxAppearance(Writer $writer, float $w, float $h, bool $checked): int
    {
        $resources = '<< >>';
        if ($checked) {
            $zaId = $this->ensureAppearanceZapfFont($writer);
            $resources = sprintf('<< /Font << /ZaDb %d 0 R >> >>', $zaId);
            // ZapfDingbats octal 064 (decimal 52) = '4' code = ✓.
            $fontSize = min($w, $h) * 0.85;
            $tx = ($w - $fontSize * 0.7) / 2.0;
            $ty = ($h - $fontSize) / 2.0 + $fontSize * 0.15;
            $content = sprintf(
                "q\n1 w\n0 0 %s %s re S\nBT\n/ZaDb %s Tf\n0 g\n%s %s Td\n(4) Tj\nET\nQ\n",
                $this->fmt($w), $this->fmt($h),
                $this->fmt($fontSize),
                $this->fmt($tx), $this->fmt($ty),
            );
        } else {
            // Empty bordered box.
            $content = sprintf(
                "q\n1 w\n0 0 %s %s re S\nQ\n",
                $this->fmt($w), $this->fmt($h),
            );
        }

        return $this->emitAppearanceStream($writer, $w, $h, $content, $resources);
    }

    /**
     * Build push/submit/reset button appearance — bordered grey box with
     * a centered caption.
     */
    private function buildButtonAppearance(Writer $writer, float $w, float $h, string $caption): int
    {
        $fontId = $this->ensureAppearanceFont($writer);
        $resources = sprintf('<< /Font << /Helv %d 0 R >> >>', $fontId);
        $fontSize = 11.0;
        // Approximate caption width — Helvetica avg. ~5pt per char @ 11pt.
        $captionWidth = strlen($caption) * $fontSize * 0.5;
        $tx = max(2.0, ($w - $captionWidth) / 2.0);
        $ty = ($h - $fontSize) / 2.0 + 1.0;
        $content = sprintf(
            "q\n0.9 0.9 0.9 rg\n0 0 %s %s re f\n0.5 w\n0 0 %s %s re S\nBT\n/Helv %s Tf\n0 g\n%s %s Td\n%s Tj\nET\nQ\n",
            $this->fmt($w), $this->fmt($h),
            $this->fmt($w), $this->fmt($h),
            $this->fmt($fontSize),
            $this->fmt($tx), $this->fmt($ty),
            $this->pdfString($caption),
        );

        return $this->emitAppearanceStream($writer, $w, $h, $content, $resources);
    }

    /**
     * Build signature widget appearance — empty bordered box.
     */
    private function buildSignatureAppearance(Writer $writer, float $w, float $h): int
    {
        $content = sprintf(
            "q\n0.5 w\n0 0 %s %s re S\nQ\n",
            $this->fmt($w), $this->fmt($h),
        );

        return $this->emitAppearanceStream($writer, $w, $h, $content, '<< >>');
    }

    /**
     * Build radio-button appearance (selected = filled circle,
     * unselected = empty circle).
     */
    private function buildRadioAppearance(Writer $writer, float $w, float $h, bool $selected): int
    {
        $resources = '<< >>';
        $cx = $w / 2.0;
        $cy = $h / 2.0;
        $r = min($w, $h) / 2.0 - 1.0;
        // Cubic-bezier circle approximation (kappa ≈ 0.5523).
        $k = $r * 0.5523;
        $border = sprintf(
            "%s %s m\n"
            ."%s %s %s %s %s %s c\n"
            ."%s %s %s %s %s %s c\n"
            ."%s %s %s %s %s %s c\n"
            ."%s %s %s %s %s %s c\n",
            $this->fmt($cx + $r), $this->fmt($cy),
            $this->fmt($cx + $r), $this->fmt($cy + $k),
            $this->fmt($cx + $k), $this->fmt($cy + $r),
            $this->fmt($cx),      $this->fmt($cy + $r),
            $this->fmt($cx - $k), $this->fmt($cy + $r),
            $this->fmt($cx - $r), $this->fmt($cy + $k),
            $this->fmt($cx - $r), $this->fmt($cy),
            $this->fmt($cx - $r), $this->fmt($cy - $k),
            $this->fmt($cx - $k), $this->fmt($cy - $r),
            $this->fmt($cx),      $this->fmt($cy - $r),
            $this->fmt($cx + $k), $this->fmt($cy - $r),
            $this->fmt($cx + $r), $this->fmt($cy - $k),
            $this->fmt($cx + $r), $this->fmt($cy),
        );
        if ($selected) {
            // Filled smaller circle inside.
            $rIn = $r * 0.55;
            $kIn = $rIn * 0.5523;
            $fill = sprintf(
                "%s %s m\n"
                ."%s %s %s %s %s %s c\n"
                ."%s %s %s %s %s %s c\n"
                ."%s %s %s %s %s %s c\n"
                ."%s %s %s %s %s %s c\n",
                $this->fmt($cx + $rIn), $this->fmt($cy),
                $this->fmt($cx + $rIn), $this->fmt($cy + $kIn),
                $this->fmt($cx + $kIn), $this->fmt($cy + $rIn),
                $this->fmt($cx),        $this->fmt($cy + $rIn),
                $this->fmt($cx - $kIn), $this->fmt($cy + $rIn),
                $this->fmt($cx - $rIn), $this->fmt($cy + $kIn),
                $this->fmt($cx - $rIn), $this->fmt($cy),
                $this->fmt($cx - $rIn), $this->fmt($cy - $kIn),
                $this->fmt($cx - $kIn), $this->fmt($cy - $rIn),
                $this->fmt($cx),        $this->fmt($cy - $rIn),
                $this->fmt($cx + $kIn), $this->fmt($cy - $rIn),
                $this->fmt($cx + $rIn), $this->fmt($cy - $kIn),
                $this->fmt($cx + $rIn), $this->fmt($cy),
            );
            $content = sprintf(
                "q\n1 w\n%sS\n0 g\n%sf\nQ\n",
                $border, $fill,
            );
        } else {
            $content = sprintf(
                "q\n1 w\n%sS\nQ\n",
                $border,
            );
        }

        return $this->emitAppearanceStream($writer, $w, $h, $content, $resources);
    }

    /**
     * Build markup annotation body
     * (Text/Highlight/Underline/StrikeOut/FreeText/Square/Circle/Line/
     * Stamp/Ink/Polygon/PolyLine).
     *
     * @param  array<string, mixed>  $ann
     */
    private function buildMarkupAnnotation(array $ann): string
    {
        $rect = sprintf(
            '[%s %s %s %s]',
            $this->fmt((float) $ann['x1']), $this->fmt((float) $ann['y1']),
            $this->fmt((float) $ann['x2']), $this->fmt((float) $ann['y2']),
        );
        $contentsPart = '';
        if (! empty($ann['contents'])) {
            $contentsPart = ' /Contents ' . $this->pdfString((string) $ann['contents']);
        }
        $colorPart = '';
        if (! empty($ann['color'])) {
            $color = $ann['color'];
            $colorPart = sprintf(' /C [%s %s %s]', $this->fmt((float) $color[0]), $this->fmt((float) $color[1]), $this->fmt((float) $color[2]));
        }
        $titlePart = '';
        if (! empty($ann['title'])) {
            $titlePart = ' /T ' . $this->pdfString((string) $ann['title']);
        }

        switch ($ann['kind']) {
            case 'text':
                return sprintf(
                    '<< /Type /Annot /Subtype /Text /Rect %s%s%s%s /Name /%s >>',
                    $rect, $contentsPart, $titlePart, $colorPart, $ann['icon'],
                );
            case 'highlight':
            case 'underline':
            case 'strikeout':
                $subtype = match ($ann['kind']) {
                    'highlight' => 'Highlight',
                    'underline' => 'Underline',
                    'strikeout' => 'StrikeOut',
                };
                // QuadPoints — 8 numbers per quad: (x1 y1) bot-left,
                // (x2 y2) bot-right, (x3 y3) top-left, (x4 y4) top-right
                // (PDF spec §12.5.6.10 ordering varies by vendor; this
                // matches the Acrobat default).
                $qp = sprintf(
                    '[%s %s %s %s %s %s %s %s]',
                    $this->fmt((float) $ann['x1']), $this->fmt((float) $ann['y2']),
                    $this->fmt((float) $ann['x2']), $this->fmt((float) $ann['y2']),
                    $this->fmt((float) $ann['x1']), $this->fmt((float) $ann['y1']),
                    $this->fmt((float) $ann['x2']), $this->fmt((float) $ann['y1']),
                );

                return sprintf(
                    '<< /Type /Annot /Subtype /%s /Rect %s /QuadPoints %s%s%s >>',
                    $subtype, $rect, $qp, $contentsPart, $colorPart,
                );
            case 'freetext':
                $fontSize = (float) ($ann['fontSize'] ?? 11.0);
                // /DA — default appearance string. Color: use /C if set, else black.
                $daColor = '0 g';
                if (! empty($ann['color'])) {
                    $color = $ann['color'];
                    $daColor = sprintf(
                        '%s %s %s rg',
                        $this->fmt((float) $color[0]),
                        $this->fmt((float) $color[1]),
                        $this->fmt((float) $color[2]),
                    );
                }
                $da = sprintf('(/Helv %s Tf %s)', $this->fmt($fontSize), $daColor);

                return sprintf(
                    '<< /Type /Annot /Subtype /FreeText /Rect %s%s /DA %s >>',
                    $rect, $contentsPart, $da,
                );
            case 'square':
            case 'circle':
                $subtype = $ann['kind'] === 'square' ? 'Square' : 'Circle';
                $bs = sprintf(' /BS << /Type /Border /W %s /S /S >>', $this->fmt((float) $ann['borderWidth']));
                $icPart = '';
                if (! empty($ann['fillColor'])) {
                    $fc = $ann['fillColor'];
                    $icPart = sprintf(' /IC [%s %s %s]',
                        $this->fmt((float) $fc[0]), $this->fmt((float) $fc[1]), $this->fmt((float) $fc[2]));
                }

                return sprintf(
                    '<< /Type /Annot /Subtype /%s /Rect %s%s%s%s%s >>',
                    $subtype, $rect, $colorPart, $icPart, $bs, $contentsPart,
                );
            case 'line':
                $linePart = sprintf(' /L [%s %s %s %s]',
                    $this->fmt((float) $ann['lineX1']), $this->fmt((float) $ann['lineY1']),
                    $this->fmt((float) $ann['lineX2']), $this->fmt((float) $ann['lineY2']),
                );
                $bs = sprintf(' /BS << /Type /Border /W %s /S /S >>', $this->fmt((float) $ann['borderWidth']));

                return sprintf(
                    '<< /Type /Annot /Subtype /Line /Rect %s%s%s%s%s >>',
                    $rect, $linePart, $colorPart, $bs, $contentsPart,
                );
            case 'stamp':
                return sprintf(
                    '<< /Type /Annot /Subtype /Stamp /Rect %s /Name /%s%s >>',
                    $rect, $ann['stampName'], $contentsPart,
                );
            case 'ink':
                $strokeArrays = [];
                foreach ($ann['inkStrokes'] as $stroke) {
                    $pts = [];
                    foreach ($stroke as [$x, $y]) {
                        $pts[] = $this->fmt((float) $x) . ' ' . $this->fmt((float) $y);
                    }
                    $strokeArrays[] = '[' . implode(' ', $pts) . ']';
                }
                $inkList = ' /InkList [' . implode(' ', $strokeArrays) . ']';
                $bs = sprintf(' /BS << /Type /Border /W %s /S /S >>', $this->fmt((float) $ann['borderWidth']));

                return sprintf(
                    '<< /Type /Annot /Subtype /Ink /Rect %s%s%s%s%s >>',
                    $rect, $inkList, $colorPart, $bs, $contentsPart,
                );
            case 'polygon':
            case 'polyline':
                $subtype = $ann['kind'] === 'polygon' ? 'Polygon' : 'PolyLine';
                $verts = [];
                foreach ($ann['vertices'] as [$vx, $vy]) {
                    $verts[] = $this->fmt((float) $vx) . ' ' . $this->fmt((float) $vy);
                }
                $verticesPart = ' /Vertices [' . implode(' ', $verts) . ']';
                $icPart = '';
                if (! empty($ann['fillColor'])) {
                    $fc = $ann['fillColor'];
                    $icPart = sprintf(' /IC [%s %s %s]',
                        $this->fmt((float) $fc[0]), $this->fmt((float) $fc[1]), $this->fmt((float) $fc[2]));
                }
                $bs = sprintf(' /BS << /Type /Border /W %s /S /S >>', $this->fmt((float) $ann['borderWidth']));

                return sprintf(
                    '<< /Type /Annot /Subtype /%s /Rect %s%s%s%s%s%s >>',
                    $subtype, $rect, $verticesPart, $colorPart, $icPart, $bs, $contentsPart,
                );
            default:
                throw new \LogicException('Unknown markup annotation kind: ' . $ann['kind']);
        }
    }

    /**
     * Build the PKCS#7 signature dictionary body with placeholders for
     * /ByteRange (4 × 10-digit zero fields, padded with spaces) and
     * /Contents (16384 hex zeros — room for an ~8KB DER PKCS#7 envelope).
     */
    private function buildSignatureDictBody(SignatureConfig $cfg): string
    {
        $optionalParts = '';
        if ($cfg->signerName !== null) {
            $optionalParts .= ' /Name ' . $this->pdfString($cfg->signerName);
        }
        if ($cfg->reason !== null) {
            $optionalParts .= ' /Reason ' . $this->pdfString($cfg->reason);
        }
        if ($cfg->location !== null) {
            $optionalParts .= ' /Location ' . $this->pdfString($cfg->location);
        }
        if ($cfg->contactInfo !== null) {
            $optionalParts .= ' /ContactInfo ' . $this->pdfString($cfg->contactInfo);
        }
        $signedAt = ' /M ' . $this->pdfString($cfg->pdfSignedAt());

        // ByteRange: 4 entries padded to 10 digits each plus one extra pad
        // space, so a post-emit substr_replace can fit the updated values.
        $byteRange = '/ByteRange [0          0          0          0         ]';
        // Contents: 16384 hex zeros (room for ~8KB PKCS#7 DER blob).
        $contents = '/Contents <' . str_repeat('0', 16384) . '>';

        return sprintf(
            '<< /Type /Sig /Filter /Adobe.PPKLite /SubFilter /adbe.pkcs7.detached '
            .'%s%s%s %s >>',
            $byteRange, $contents, $signedAt, $optionalParts,
        );
    }

    /**
     * For /Dest references — use bytestring form `(name)` consistently
     * with the Names tree (matches ISO 32000-1 §12.3.2.3 — destinations
     * referenced by name resolve via /Names).
     */
    private function pdfNameString(string $name): string
    {
        return $this->pdfString($name);
    }

    /**
     * Format a float without trailing zeros and with a locale-independent
     * decimal separator.
     */
    private function fmt(float $n): string
    {
        if ((int) $n == $n) {
            return (string) (int) $n;
        }

        return rtrim(rtrim(sprintf('%.4f', $n), '0'), '.');
    }
}
