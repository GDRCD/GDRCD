<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * Applies a stream's `/Filter` chain (with `/DecodeParms`) to produce decoded
 * bytes (ISO 32000-1 §7.4).
 *
 * Filter and parameter values may themselves be indirect references, so the
 * decoder needs a {@see ReaderDocument} to dereference them. Image compression
 * filters (DCT/JPX/CCITT/JBIG2) are treated as terminal: decoding stops and
 * the still-encoded bytes are returned as-is (merge re-embeds them verbatim).
 */
final class StreamDecoder
{
    private const IMAGE_FILTERS = ['DCTDecode', 'DCT', 'JPXDecode', 'CCITTFaxDecode', 'CCF', 'JBIG2Decode'];

    public function __construct(private readonly ReaderDocument $doc)
    {
    }

    /**
     * Decode a stream fully, or up to the first terminal image filter.
     */
    public function decode(PdfStream $stream): string
    {
        $data = $stream->raw;
        $dict = $stream->dict;

        $filters = $this->normalizeToList($this->doc->deref($dict->get('Filter')));
        $parms = $this->normalizeParmsList(
            $this->doc->deref($dict->get('DecodeParms') ?? $dict->get('DP')),
            count($filters),
        );

        foreach ($filters as $i => $filter) {
            $name = $filter instanceof PdfName ? $filter->value : (string) $filter;
            if (in_array($name, self::IMAGE_FILTERS, true)) {
                break; // terminal: leave the remaining (image) bytes encoded
            }
            $data = $this->applyFilter($name, $data, $parms[$i]);
        }

        return $data;
    }

    private function applyFilter(string $name, string $data, ?PdfDictionary $parms): string
    {
        switch ($name) {
            case 'FlateDecode':
            case 'Fl':
                $data = Filters::flate($data);
                return $this->predict($data, $parms);

            case 'LZWDecode':
            case 'LZW':
                $early = $parms !== null ? $this->deInt($parms->get('EarlyChange'), 1) : 1;
                $data = Filters::lzw($data, $early);
                return $this->predict($data, $parms);

            case 'ASCII85Decode':
            case 'A85':
                return Filters::ascii85($data);

            case 'ASCIIHexDecode':
            case 'AHx':
                return Filters::asciiHex($data);

            case 'RunLengthDecode':
            case 'RL':
                return Filters::runLength($data);

            default:
                throw new PdfParseException("Unsupported stream filter: {$name}");
        }
    }

    private function predict(string $data, ?PdfDictionary $parms): string
    {
        if ($parms === null) {
            return $data;
        }
        $predictor = $this->deInt($parms->get('Predictor'), 1);
        if ($predictor <= 1) {
            return $data;
        }
        return Filters::applyPredictor(
            $data,
            $predictor,
            $this->deInt($parms->get('Colors'), 1),
            $this->deInt($parms->get('BitsPerComponent'), 8),
            $this->deInt($parms->get('Columns'), 1),
        );
    }

    /** @return list<mixed> */
    private function normalizeToList(mixed $value): array
    {
        if ($value === null || $value instanceof PdfNull) {
            return [];
        }
        if (is_array($value)) {
            return array_values($value);
        }
        return [$value];
    }

    /**
     * Align /DecodeParms with the filter list: a single dict applies to the one
     * filter; an array runs parallel; missing entries are null.
     *
     * @return list<?PdfDictionary>
     */
    private function normalizeParmsList(mixed $value, int $filterCount): array
    {
        $out = array_fill(0, max($filterCount, 0), null);
        if ($value === null || $value instanceof PdfNull) {
            return $out;
        }
        if (is_array($value)) {
            foreach (array_values($value) as $i => $p) {
                $p = $this->doc->deref($p);
                $out[$i] = $p instanceof PdfDictionary ? $p : null;
            }
            return $out;
        }
        if ($value instanceof PdfDictionary && $filterCount >= 1) {
            $out[0] = $value;
        }
        return $out;
    }

    private function deInt(mixed $value, int $default): int
    {
        $value = $this->doc->deref($value);
        return is_int($value) ? $value : $default;
    }
}
