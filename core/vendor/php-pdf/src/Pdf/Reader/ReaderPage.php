<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * A single leaf page resolved from the page tree, with its inheritable
 * attributes (Resources, MediaBox, CropBox, Rotate) already flattened
 * (ISO 32000-1 §7.7.3.3–§7.7.3.4).
 */
final readonly class ReaderPage
{
    /**
     * @param int              $objectNumber the leaf /Page object number (-1 if inline)
     * @param PdfDictionary    $dict         the raw page dictionary
     * @param array{float,float,float,float} $mediaBox
     * @param array{float,float,float,float} $cropBox  defaults to MediaBox
     * @param int              $rotate       normalized to {0,90,180,270}
     * @param PdfDictionary|null $resources  effective /Resources, or null
     */
    public function __construct(
        public int $objectNumber,
        public PdfDictionary $dict,
        public array $mediaBox,
        public array $cropBox,
        public int $rotate,
        public ?PdfDictionary $resources,
    ) {
    }

    public function width(): float
    {
        return abs($this->cropBox[2] - $this->cropBox[0]);
    }

    public function height(): float
    {
        return abs($this->cropBox[3] - $this->cropBox[1]);
    }
}
