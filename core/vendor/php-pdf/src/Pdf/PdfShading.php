<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * PDF Shading object (ISO 32000-1 §8.7.4).
 *
 * Type 2 (axial) — linear gradient between (x0, y0) and (x1, y1).
 * Type 3 (radial) — radial gradient between circles (cx0, cy0, r0) and
 * (cx1, cy1, r1).
 *
 * Color interpolation through PdfFunction child.
 */
final readonly class PdfShading
{
    public const TYPE_AXIAL = 2;

    public const TYPE_RADIAL = 3;

    /**
     * @param  list<float>  $coords  [x0, y0, x1, y1] for axial;
     *                                 [cx0, cy0, r0, cx1, cy1, r1] for radial.
     */
    public function __construct(
        public int $shadingType,
        public array $coords,
        public PdfFunction|PdfStitchingFunction $function,
    ) {
        if ($shadingType !== self::TYPE_AXIAL && $shadingType !== self::TYPE_RADIAL) {
            throw new \InvalidArgumentException('PdfShading supports type 2 (axial) or 3 (radial)');
        }
    }

    /**
     * Body of /Type /Shading dict — note Function references separately
     * emitted object (caller fills /Function entry).
     */
    public function toDictBody(int $functionObjId): string
    {
        $fmt = static fn (float $f): string => rtrim(rtrim(sprintf('%.4F', $f), '0'), '.') ?: '0';
        $coordsStr = implode(' ', array_map($fmt, $this->coords));

        return sprintf(
            '<< /ShadingType %d /ColorSpace /DeviceRGB /Coords [%s] '
            .'/Function %d 0 R /Extend [true true] >>',
            $this->shadingType, $coordsStr, $functionObjId,
        );
    }
}
