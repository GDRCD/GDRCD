<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * PDF Function object (ISO 32000-1 §7.10).
 *
 * Type 2 — exponential interpolation between two color values.
 * Used by axial/radial shadings for 2-stop linear gradients.
 *
 * Multi-stop gradients support — see PdfStitchingFunction (Type 3).
 */
final readonly class PdfFunction
{
    public const TYPE_EXPONENTIAL = 2;

    /**
     * @param  array{0: float, 1: float, 2: float}  $c0  Start color (RGB 0..1).
     * @param  array{0: float, 1: float, 2: float}  $c1  End color (RGB 0..1).
     * @param  float  $n  Interpolation exponent (1 = linear).
     */
    public function __construct(
        public array $c0,
        public array $c1,
        public float $n = 1.0,
    ) {}

    public function toDictBody(): string
    {
        $fmt = static function (float $f): string {
            return rtrim(rtrim(sprintf('%.4F', $f), '0'), '.') ?: '0';
        };

        return sprintf(
            '<< /FunctionType 2 /Domain [0 1] /C0 [%s %s %s] /C1 [%s %s %s] /N %s >>',
            $fmt($this->c0[0]), $fmt($this->c0[1]), $fmt($this->c0[2]),
            $fmt($this->c1[0]), $fmt($this->c1[1]), $fmt($this->c1[2]),
            $fmt($this->n),
        );
    }
}
