<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * Tiling Pattern (Type 1) — repeating fill pattern.
 *
 * ISO 32000-1 §8.7.3. A pattern cell with bbox + xStep/yStep defines
 * the repeat geometry. Filled regions are tiled with the pattern stream.
 *
 * Layout in output PDF:
 *   N 0 obj
 *     << /Type /Pattern /PatternType 1 /PaintType 1 /TilingType 1
 *        /BBox [llx lly urx ury] /XStep w /YStep h /Resources <<>> /Length L >>
 *     stream
 *       <pattern cell drawing commands>
 *     endstream
 *   endobj
 *
 * PaintType 1 (colored): pattern stream contains color ops; reference via
 * `/Pattern cs /Name scn`.
 *
 * Not supported:
 *  - PaintType 2 (uncolored — color set externally per use).
 *  - Pattern-specific /Resources (no font/image inside pattern cell).
 */
final readonly class PdfTilingPattern
{
    public function __construct(
        public string $contentStream,
        public float $bboxLlx,
        public float $bboxLly,
        public float $bboxUrx,
        public float $bboxUry,
        public float $xStep,
        public float $yStep,
        /** @var list<float>|null Optional /Matrix [a b c d e f] for rotated/sheared tile. */
        public ?array $matrix = null,
    ) {
        if ($bboxUrx <= $bboxLlx || $bboxUry <= $bboxLly) {
            throw new \InvalidArgumentException('Tiling pattern /BBox must have positive width/height');
        }
        if ($xStep == 0.0 || $yStep == 0.0) {
            throw new \InvalidArgumentException('Tiling pattern /XStep and /YStep must be non-zero');
        }
        if ($matrix !== null && count($matrix) !== 6) {
            throw new \InvalidArgumentException('Tiling pattern /Matrix must have 6 entries');
        }
    }

    /**
     * Build the dictionary head fragment (without /Length and stream body).
     */
    public function dictHead(): string
    {
        $bbox = sprintf(
            '[%s %s %s %s]',
            self::fmt($this->bboxLlx), self::fmt($this->bboxLly),
            self::fmt($this->bboxUrx), self::fmt($this->bboxUry),
        );
        $matrixPart = '';
        if ($this->matrix !== null) {
            $matrixPart = ' /Matrix ['
                . implode(' ', array_map([self::class, 'fmt'], $this->matrix))
                . ']';
        }

        return sprintf(
            '/Type /Pattern /PatternType 1 /PaintType 1 /TilingType 1 '
            .'/BBox %s /XStep %s /YStep %s /Resources << >>%s',
            $bbox, self::fmt($this->xStep), self::fmt($this->yStep), $matrixPart,
        );
    }

    private static function fmt(float $v): string
    {
        if ($v == floor($v) && abs($v) < 1e9) {
            return (string) (int) $v;
        }

        return rtrim(rtrim(sprintf('%.4f', $v), '0'), '.');
    }
}
