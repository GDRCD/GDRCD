<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * PDF Type 3 stitching function (ISO 32000-1 §7.10.4).
 *
 * Composes N sub-functions sequentially across domain [0, 1]:
 *  - At t = bounds[0], first sub-function active.
 *  - Sub-function inputs mapped via /Encode entries.
 *
 * Used for multi-stop gradients: each pair of adjacent stops → Type 2
 * sub-function, stitched together by Type 3.
 */
final readonly class PdfStitchingFunction
{
    /**
     * @param  list<PdfFunction>  $subFunctions  N functions, one per stop pair.
     * @param  list<float>  $bounds  (N - 1) values — split points in [0,1].
     * @param  list<float>  $encode  2N values: per sub-function input range.
     */
    public function __construct(
        public array $subFunctions,
        public array $bounds,
        public array $encode,
    ) {
        if (count($subFunctions) < 1) {
            throw new \InvalidArgumentException('PdfStitchingFunction requires ≥1 sub-functions');
        }
        if (count($bounds) !== count($subFunctions) - 1) {
            throw new \InvalidArgumentException('Bounds count must = subFunctions count - 1');
        }
        if (count($encode) !== 2 * count($subFunctions)) {
            throw new \InvalidArgumentException('Encode array length must = 2 × subFunctions');
        }
    }

    /**
     * Render Type 3 dict body, referencing sub-functions by object ID.
     *
     * @param  list<int>  $subFunctionIds
     */
    public function toDictBody(array $subFunctionIds): string
    {
        if (count($subFunctionIds) !== count($this->subFunctions)) {
            throw new \InvalidArgumentException('subFunctionIds count must match subFunctions');
        }
        $fmt = static fn (float $f): string => rtrim(rtrim(sprintf('%.4F', $f), '0'), '.') ?: '0';
        $funcs = implode(' ', array_map(fn ($id) => "$id 0 R", $subFunctionIds));
        $boundsStr = implode(' ', array_map($fmt, $this->bounds));
        $encodeStr = implode(' ', array_map($fmt, $this->encode));

        return sprintf(
            '<< /FunctionType 3 /Domain [0 1] /Functions [%s] '
            .'/Bounds [%s] /Encode [%s] >>',
            $funcs, $boundsStr, $encodeStr,
        );
    }
}
