<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

/**
 * Supported barcode formats.
 *
 * Linear (1D):
 *  - Code 128 (Set A/B/C auto-switching + GS1-128)
 *  - Code 39 alphanumeric self-checking
 *  - Code 93 dense alphanumeric with dual Mod-47 check
 *  - Code 11 numeric + dash, telecom labeling
 *  - Codabar (NW-7) numeric + punct, libraries / FedEx / blood banks
 *  - ITF (Interleaved 2 of 5), incl. ITF-14 GTIN profile
 *  - EAN-8, EAN-13 (+ EAN-2/5 add-on supplements)
 *  - UPC-A, UPC-E (zero-suppressed)
 *  - MSI Plessey, retail shelf labeling
 *  - Pharmacode (Laetus), pharma blister packs
 *
 * 2D:
 *  - QR Code V1-V10 (Numeric, Alphanumeric, Byte, Kanji + ECI +
 *    Structured Append + FNC1 GS1/AIM modes)
 *  - Data Matrix ECC 200 (all standard sizes incl. 144×144,
 *    rectangular variants, 6 encoding modes, Macro 05/06, GS1, ECI)
 *  - PDF417 (Byte / Text / Numeric compaction + Macro + GS1 + ECI)
 *  - Aztec (Compact 1-4L + Full 5-32L + Structured Append + FLG/ECI)
 */
enum BarcodeFormat: string
{
    case Code128 = 'code128';
    case Code11 = 'code11';
    case Code39 = 'code39';
    case Code93 = 'code93';
    case Codabar = 'codabar';
    case MsiPlessey = 'msi';
    case Pharmacode = 'pharmacode';
    case Itf = 'itf';
    case Ean13 = 'ean13';
    case Ean8 = 'ean8';
    case UpcA = 'upca';
    case UpcE = 'upce';
    case Qr = 'qr';
    case DataMatrix = 'datamatrix';
    case Pdf417 = 'pdf417';
    case Aztec = 'aztec';

    public function is2D(): bool
    {
        return match ($this) {
            self::Qr, self::DataMatrix, self::Aztec => true,
            default => false,
        };
    }

    /**
     * Stacked-linear barcodes (currently PDF417) need per-row rendering
     * rather than a single bar sequence.
     */
    public function isStacked(): bool
    {
        return $this === self::Pdf417;
    }
}
