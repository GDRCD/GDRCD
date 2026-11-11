<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * PDF Pattern object (ISO 32000-1 §8.7).
 *
 * Type 2 (shading pattern) — references PdfShading. Used for gradient
 * fills via SVG linearGradient / radialGradient.
 *
 * Optional /Matrix transform applied to the pattern coordinate system —
 * used for the SVG gradientTransform attribute.
 *
 * Type 1 (tiling pattern) — for repeated tiles. Not implemented — use case
 * rare; SVG patterns mostly cover gradient needs.
 */
final readonly class PdfPattern
{
    public const TYPE_SHADING = 2;

    /**
     * @param  array{0: float, 1: float, 2: float, 3: float, 4: float, 5: float}|null  $matrix  PDF 2×3 matrix [a b c d e f].
     */
    public function __construct(
        public PdfShading $shading,
        public ?array $matrix = null,
    ) {}

    /**
     * Body of /Type /Pattern /PatternType 2 dict.
     * Caller emits Shading separately; passes its object ID.
     */
    public function toDictBody(int $shadingObjId): string
    {
        $matrixPart = '';
        if ($this->matrix !== null) {
            $fmt = static fn (float $f): string => rtrim(rtrim(sprintf('%.4F', $f), '0'), '.') ?: '0';
            $matrixPart = sprintf(
                ' /Matrix [%s %s %s %s %s %s]',
                $fmt($this->matrix[0]), $fmt($this->matrix[1]),
                $fmt($this->matrix[2]), $fmt($this->matrix[3]),
                $fmt($this->matrix[4]), $fmt($this->matrix[5]),
            );
        }

        return sprintf(
            '<< /Type /Pattern /PatternType 2 /Shading %d 0 R%s >>',
            $shadingObjId, $matrixPart,
        );
    }
}
