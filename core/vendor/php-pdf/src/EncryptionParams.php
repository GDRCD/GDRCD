<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf;

use Dskripchenko\PhpPdf\Pdf\Encryption;
use Dskripchenko\PhpPdf\Pdf\EncryptionAlgorithm;

/**
 * Document-level encryption parameters value object.
 *
 * Pass to `Document::__construct(encryption: new EncryptionParams(...))`
 * for declarative encryption setup without dropping to the low-level
 * Pdf\Document API. Constructor mirrors `Pdf\Document::encrypt()`.
 *
 * Default algorithm is RC4-128 (V2 R3) for broad reader compatibility.
 * Use AES-128 (V4 R4, PDF 1.6) or AES-256 R6 (V5 R6, PDF 2.0) for new
 * documents needing modern crypto.
 */
final readonly class EncryptionParams
{
    public function __construct(
        public string $userPassword,
        public ?string $ownerPassword = null,
        public int $permissions = Encryption::PERM_PRINT | Encryption::PERM_COPY | Encryption::PERM_PRINT_HIGH,
        public EncryptionAlgorithm $algorithm = EncryptionAlgorithm::Rc4_128,
    ) {}
}
