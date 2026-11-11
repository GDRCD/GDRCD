<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Barcode\QrEccLevel;
use Dskripchenko\PhpPdf\Barcode\QrEncodingMode;
use Dskripchenko\PhpPdf\Style\Alignment;

/**
 * Block-level barcode element.
 *
 * Renders as a set of black bars (or a 2D module grid) with an optional
 * human-readable caption underneath.
 *
 * `$widthPt` overrides the auto-computed width (modules × 1pt by default).
 * `$eccLevel` and `$qrMode` apply only to QR codes; linear barcodes
 * ignore them.
 */
final readonly class Barcode implements BlockElement
{
    public function __construct(
        public string $value,
        public BarcodeFormat $format = BarcodeFormat::Code128,
        public ?float $widthPt = null,
        public float $heightPt = 40.0,
        public bool $showText = true,
        public float $textSizePt = 8.0,
        public Alignment $alignment = Alignment::Start,
        public float $spaceBeforePt = 0,
        public float $spaceAfterPt = 0,
        public ?QrEccLevel $eccLevel = null,
        public ?QrEncodingMode $qrMode = null,
    ) {}
}
