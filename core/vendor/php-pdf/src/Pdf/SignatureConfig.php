<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * PKCS#7 detached signature configuration (ISO 32000-1 §12.8.1).
 *
 * Used with `Pdf\Document::sign()`. The signed bytes cover the whole PDF
 * except the /Contents hex string, which is patched with the signature
 * after the document body is fully emitted.
 *
 * Certificate and private key are accepted as PEM strings. Optional
 * fields (`signerName`, `reason`, `location`, `contactInfo`, `signedAt`)
 * populate the corresponding entries of the signature dictionary per
 * PDF spec §12.8.1.
 */
final readonly class SignatureConfig
{
    public function __construct(
        public string $certPem,
        public string $privateKeyPem,
        public ?string $privateKeyPassphrase = null,
        public ?string $signerName = null,
        public ?string $reason = null,
        public ?string $location = null,
        public ?string $contactInfo = null,
        public ?\DateTimeImmutable $signedAt = null,
    ) {
        if (! function_exists('openssl_pkcs7_sign')) {
            throw new \RuntimeException('PKCS#7 signing requires the openssl extension');
        }
    }

    public function effectiveSignedAt(): \DateTimeImmutable
    {
        return $this->signedAt ?? new \DateTimeImmutable;
    }

    /**
     * Signing time formatted per PDF spec: `D:YYYYMMDDHHMMSS+HH'MM'`.
     */
    public function pdfSignedAt(): string
    {
        $dt = $this->effectiveSignedAt();
        $tz = $dt->format('P');            // e.g. +03:00
        $tzPdf = str_replace(':', "'", $tz) . "'"; // → +03'00'

        return 'D:' . $dt->format('YmdHis') . $tzPdf;
    }
}
