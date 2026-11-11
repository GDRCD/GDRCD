<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * Supported PDF encryption algorithms.
 */
enum EncryptionAlgorithm: string
{
    /** ISO 32000-1 V=2 R=3 — RC4 stream cipher with a 128-bit key. */
    case Rc4_128 = 'rc4-128';

    /** ISO 32000-1 V=4 R=4 — AES-128-CBC via AESV2 crypt filter. */
    case Aes_128 = 'aes-128';

    /** Adobe Supplement to ISO 32000 V=5 R=5 — AES-256-CBC. */
    case Aes_256 = 'aes-256';

    /** ISO 32000-2 (PDF 2.0) V=5 R=6 — AES-256-CBC + iterative Algorithm 2.B hash. */
    case Aes_256_R6 = 'aes-256-r6';

    public function pdfVersion(): int
    {
        return match ($this) {
            self::Rc4_128 => 2,
            self::Aes_128 => 4,
            self::Aes_256, self::Aes_256_R6 => 5,
        };
    }

    public function revision(): int
    {
        return match ($this) {
            self::Rc4_128 => 3,
            self::Aes_128 => 4,
            self::Aes_256 => 5,
            self::Aes_256_R6 => 6,
        };
    }
}
