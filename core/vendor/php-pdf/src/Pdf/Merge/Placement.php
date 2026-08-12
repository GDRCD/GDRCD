<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Merge;

/**
 * Describes how an embedded page (a Form XObject sized W×H in points) is placed
 * onto a target page's MediaBox. Produces the `cm` transform matrix used before
 * the `Do` operator.
 */
final class Placement
{
    private const FIT = 'fit';
    private const STRETCH = 'stretch';
    private const AT = 'at';

    private function __construct(
        private readonly string $mode,
        private readonly float $x = 0.0,
        private readonly float $y = 0.0,
        private readonly float $scale = 1.0,
    ) {
    }

    /** Scale to fit the target page, preserving aspect ratio, centered. */
    public static function fit(): self
    {
        return new self(self::FIT);
    }

    /** Stretch to fill the target page exactly (aspect ratio not preserved). */
    public static function stretch(): self
    {
        return new self(self::STRETCH);
    }

    /** Place the embedded page's lower-left corner at (x, y), scaled by $scale. */
    public static function at(float $x, float $y, float $scale = 1.0): self
    {
        return new self(self::AT, $x, $y, $scale);
    }

    /**
     * @param array{float,float,float,float} $mediaBox target page box
     * @return array{float,float,float,float,float,float} cm matrix [a b c d e f]
     */
    public function cm(float $w, float $h, array $mediaBox): array
    {
        $mx = $mediaBox[0];
        $my = $mediaBox[1];
        $mw = $mediaBox[2] - $mediaBox[0];
        $mh = $mediaBox[3] - $mediaBox[1];

        return match ($this->mode) {
            self::STRETCH => [
                $w > 0 ? $mw / $w : 1.0, 0.0, 0.0, $h > 0 ? $mh / $h : 1.0, $mx, $my,
            ],
            self::AT => [$this->scale, 0.0, 0.0, $this->scale, $this->x, $this->y],
            default => $this->fitMatrix($w, $h, $mx, $my, $mw, $mh),
        };
    }

    /**
     * @return array{float,float,float,float,float,float}
     */
    private function fitMatrix(float $w, float $h, float $mx, float $my, float $mw, float $mh): array
    {
        $s = ($w > 0 && $h > 0) ? min($mw / $w, $mh / $h) : 1.0;
        $tx = $mx + ($mw - $s * $w) / 2;
        $ty = $my + ($mh - $s * $h) / 2;
        return [$s, 0.0, 0.0, $s, $tx, $ty];
    }
}
