<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf;

use Dskripchenko\PhpPdf\Layout\Engine;

/**
 * High-level PDF document — immutable AST root.
 *
 * Holds one primary Section plus optional additional sections. Rendering
 * goes through Layout\Engine which produces Pdf\Document bytes.
 *
 * Use `toBytes()` for in-memory output, `toStream()` for streaming output
 * to a resource (memory-efficient for large documents), or `toFile()` for
 * direct file output.
 *
 * `fromHtml()` is a convenience factory that parses HTML5 markup with
 * inline CSS into a Document. See `Html\HtmlParser` for supported tags.
 */
final readonly class Document
{
    /**
     * @param  array<string, string>  $metadata  PDF /Info dict fields:
     *                                            Title, Author, Subject,
     *                                            Keywords, Creator, Producer.
     * @param  list<Section>  $additionalSections  Extra sections rendered
     *                                              after the primary one.
     *                                              Each starts on a new page
     *                                              with its own PageSetup,
     *                                              header, footer, watermark.
     */
    public function __construct(
        public Section $section,
        public array $metadata = [],
        public array $additionalSections = [],
        /** Enable Tagged PDF (accessibility) during emission. */
        public bool $tagged = false,
        /**
         * Document language hint (BCP 47 tag — e.g. 'en', 'ru', 'en-US').
         * Emitted as /Lang in Catalog. Required by PDF/UA for tagged
         * documents — used by screen readers as default speech locale.
         */
        public ?string $lang = null,
        /**
         * Emit cross-reference table as a PDF 1.5 XRef stream object instead
         * of the classic `xref...trailer` keywords. Reduces metadata size by
         * ~50%. Falls back to classic xref when PKCS#7 signing is configured.
         */
        public bool $useXrefStream = false,
        /**
         * Target PDF version (header `%PDF-X.Y`). Null lets the engine pick a
         * default (1.7) with auto-bumps when subsystems require it
         * (AES-128 → 1.6, AES-256 → 1.7, PDF 2.0 features → 2.0, XRef stream
         * → 1.5). Set explicitly for legacy reader compatibility (e.g. '1.4').
         */
        public ?string $pdfVersion = null,
        /**
         * Emit Object Streams (PDF 1.5+) — pack uncompressed dict objects
         * into a single FlateDecode-compressed stream. Saves ~15-30% output
         * size beyond XRef streams. Auto-enables `useXrefStream`. Disabled
         * automatically when signing or encrypting.
         */
        public bool $useObjectStreams = false,
        /**
         * Declarative encryption. Null = no encryption. Otherwise applies
         * password, permissions, and algorithm during emission.
         */
        public ?EncryptionParams $encryption = null,
        /**
         * Declarative PKCS#7 detached signing. Requires an AcroForm with a
         * signature placeholder field. Null = no signing.
         */
        public ?\Dskripchenko\PhpPdf\Pdf\SignatureConfig $signature = null,
        /**
         * Declarative PDF/A conformance. Null = no PDF/A. Applying
         * conformance='A' auto-enables Tagged PDF.
         */
        public ?\Dskripchenko\PhpPdf\Pdf\PdfAConfig $pdfA = null,
        /**
         * Declarative PDF/X-1a/X-3/X-4 print conformance. Mutually exclusive
         * with `pdfA` and `encryption`.
         */
        public ?\Dskripchenko\PhpPdf\Pdf\PdfXConfig $pdfX = null,
    ) {}

    /**
     * @return list<Section>
     */
    public function sections(): array
    {
        return [$this->section, ...$this->additionalSections];
    }

    /**
     * Parse HTML markup and wrap it in a Document.
     *
     * Supported HTML5 subset:
     *  - Block tags: p, div, section, article, h1-h6, hr, ul/ol/li,
     *    table/tr/td/th (with thead/tbody/tfoot wrappers), blockquote, pre,
     *    header, footer, nav, aside, main, figure, figcaption, address,
     *    details/summary, dl/dt/dd, center
     *  - Inline tags: span, b/strong, i/em, u, s/strike/del, sup/sub, br,
     *    wbr, img, picture, a, font, svg, code, kbd, samp, tt, var, mark,
     *    small, big, ins, cite, dfn, q, abbr
     *  - Inline CSS via the `style` attribute: color, background-color,
     *    font-size, font-family, font-weight, font-style, text-decoration,
     *    text-transform, letter-spacing
     *  - Block CSS: text-align, margin, padding, line-height, text-indent,
     *    border (shorthand and per-side)
     *
     * Not supported: external CSS, `<style>` blocks, complex selectors,
     * `@media` queries, JavaScript, forms, absolute/floats. Preprocess via
     * an external CSS inliner (e.g. tijsverkoyen/css-to-inline-styles) to
     * convert `<style>` blocks to inline before parsing.
     *
     * @param  string  $html  HTML markup. No outer `<html>/<body>` wrapper required.
     * @param  array<string, string>  $metadata  Optional /Info fields.
     */
    public static function fromHtml(
        string $html,
        array $metadata = [],
        ?Section $sectionTemplate = null,
        bool $tagged = false,
        ?string $lang = null,
        bool $useXrefStream = false,
        ?string $pdfVersion = null,
        bool $useObjectStreams = false,
        ?EncryptionParams $encryption = null,
        ?\Dskripchenko\PhpPdf\Pdf\SignatureConfig $signature = null,
        ?\Dskripchenko\PhpPdf\Pdf\PdfAConfig $pdfA = null,
    ): self {
        $parser = new \Dskripchenko\PhpPdf\Html\HtmlParser;
        $blocks = $parser->parse($html);

        if ($sectionTemplate !== null) {
            // Preserve template's page setup, headers, footers, watermark;
            // replace body with parsed blocks.
            $section = new Section(
                body: $blocks,
                pageSetup: $sectionTemplate->pageSetup,
                headerBlocks: $sectionTemplate->headerBlocks,
                footerBlocks: $sectionTemplate->footerBlocks,
                watermarkText: $sectionTemplate->watermarkText,
                firstPageHeaderBlocks: $sectionTemplate->firstPageHeaderBlocks,
                firstPageFooterBlocks: $sectionTemplate->firstPageFooterBlocks,
                watermarkImage: $sectionTemplate->watermarkImage,
                watermarkImageWidthPt: $sectionTemplate->watermarkImageWidthPt,
                watermarkImageOpacity: $sectionTemplate->watermarkImageOpacity,
                watermarkTextOpacity: $sectionTemplate->watermarkTextOpacity,
            );
        } else {
            $section = new Section($blocks);
        }

        return new self(
            section: $section,
            metadata: $metadata,
            tagged: $tagged,
            lang: $lang,
            useXrefStream: $useXrefStream,
            pdfVersion: $pdfVersion,
            useObjectStreams: $useObjectStreams,
            encryption: $encryption,
            signature: $signature,
            pdfA: $pdfA,
        );
    }

    /**
     * Merge multiple Documents into a single output (batch generation case).
     *
     * Sections are concatenated in order. Each section keeps its own
     * PageSetup, producing natural section breaks between sources.
     * Document-level configuration (metadata, tagged, useXrefStream, etc.)
     * is inherited from the first input; subsequent documents' top-level
     * settings are ignored.
     *
     * @param  list<self>  $documents
     */
    public static function concat(array $documents): self
    {
        if ($documents === []) {
            throw new \InvalidArgumentException('Document::concat requires at least one document');
        }
        $first = $documents[0];
        $sections = [];
        foreach ($documents as $doc) {
            if (! $doc instanceof self) {
                throw new \InvalidArgumentException('All elements must be Document instances');
            }
            foreach ($doc->sections() as $s) {
                $sections[] = $s;
            }
        }
        $primarySection = $sections[0];
        $additional = array_slice($sections, 1);

        return new self(
            section: $primarySection,
            metadata: $first->metadata,
            additionalSections: $additional,
            tagged: $first->tagged,
            lang: $first->lang,
            useXrefStream: $first->useXrefStream,
            pdfVersion: $first->pdfVersion,
            useObjectStreams: $first->useObjectStreams,
            encryption: $first->encryption,
            signature: $first->signature,
            pdfA: $first->pdfA,
        );
    }

    /**
     * Render the document and return its raw PDF bytes.
     */
    public function toBytes(?Engine $engine = null): string
    {
        return $this->prepare($engine)->toBytes();
    }

    /**
     * Render the document directly to a writable stream resource. Avoids
     * accumulating the entire output in memory.
     *
     * @param  resource  $stream
     * @return int  Bytes written.
     */
    public function toStream($stream, ?Engine $engine = null): int
    {
        return $this->prepare($engine)->toStream($stream);
    }

    /**
     * Render the document directly to a file. Uses streaming output
     * internally for memory efficiency.
     */
    public function toFile(string $path, ?Engine $engine = null): int
    {
        $fp = @fopen($path, 'wb');
        if ($fp === false) {
            throw new \RuntimeException('Failed to open PDF file for writing: '.$path);
        }
        try {
            return $this->toStream($fp, $engine);
        } finally {
            fclose($fp);
        }
    }

    /**
     * Run the layout engine and apply post-render flags (metadata, xref
     * stream, object streams, encryption, signing, PDF/A, PDF/X).
     *
     * PDF/A and PDF/X must be applied before encryption — they enforce
     * mutual exclusion via LogicException.
     */
    private function prepare(?Engine $engine): \Dskripchenko\PhpPdf\Pdf\Document
    {
        $engine ??= new Engine;
        $pdf = $engine->render($this);
        if ($this->pdfVersion !== null) {
            $pdf->pdfVersion($this->pdfVersion);
        }
        if ($this->metadata !== []) {
            $pdf->metadata(
                title: $this->metadata['Title'] ?? null,
                author: $this->metadata['Author'] ?? null,
                subject: $this->metadata['Subject'] ?? null,
                keywords: $this->metadata['Keywords'] ?? null,
                creator: $this->metadata['Creator'] ?? null,
                producer: $this->metadata['Producer'] ?? null,
            );
        }
        if ($this->pdfA !== null) {
            $pdf->enablePdfA($this->pdfA);
        }
        if ($this->pdfX !== null) {
            $pdf->enablePdfX($this->pdfX);
        }
        if ($this->encryption !== null) {
            $pdf->encrypt(
                userPassword: $this->encryption->userPassword,
                ownerPassword: $this->encryption->ownerPassword,
                permissions: $this->encryption->permissions,
                algorithm: $this->encryption->algorithm,
            );
        }
        if ($this->signature !== null) {
            $pdf->sign($this->signature);
        }
        if ($this->useXrefStream) {
            $pdf->useXrefStream();
        }
        if ($this->useObjectStreams) {
            $pdf->useObjectStreams();
        }

        return $pdf;
    }
}
