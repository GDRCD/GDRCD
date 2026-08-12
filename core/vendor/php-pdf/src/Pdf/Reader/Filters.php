<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * Stateless stream-filter decoders (ISO 32000-1 §7.4).
 *
 * Each method takes already-extracted raw bytes and returns the decoded bytes.
 * Predictor post-processing (PNG/TIFF, §7.4.4.4) is applied separately by
 * {@see applyPredictor()} because it is shared by Flate and LZW.
 *
 * Image compression filters (DCT/JPX/CCITT/JBIG2) are intentionally *not*
 * decoded here — merge copies those streams verbatim.
 */
final class Filters
{
    /** FlateDecode (§7.4.4). */
    public static function flate(string $data): string
    {
        // zlib-wrapped first; fall back to raw deflate for lenient producers.
        $out = @gzuncompress($data);
        if ($out === false) {
            $out = @gzinflate($data);
        }
        if ($out === false) {
            throw new PdfParseException('FlateDecode: corrupt zlib/deflate stream');
        }
        return $out;
    }

    /** ASCIIHexDecode (§7.4.2). */
    public static function asciiHex(string $data): string
    {
        $hex = '';
        $len = strlen($data);
        for ($i = 0; $i < $len; $i++) {
            $c = $data[$i];
            if ($c === '>') {
                break;
            }
            if (ctype_xdigit($c)) {
                $hex .= $c;
            }
            // Whitespace and other bytes are ignored per spec.
        }
        if (strlen($hex) % 2 === 1) {
            $hex .= '0';
        }
        return (string) hex2bin($hex);
    }

    /** ASCII85Decode (§7.4.3). */
    public static function ascii85(string $data): string
    {
        $out = '';
        $tuple = 0;
        $count = 0;
        $len = strlen($data);

        for ($i = 0; $i < $len; $i++) {
            $c = $data[$i];
            if ($c === '~') {
                break; // '~>' end-of-data
            }
            if ($c === 'z' && $count === 0) {
                $out .= "\0\0\0\0";
                continue;
            }
            $ord = ord($c);
            if ($ord < 33 || $ord > 117) {
                continue; // whitespace / noise
            }
            $tuple = $tuple * 85 + ($ord - 33);
            if (++$count === 5) {
                $out .= chr(($tuple >> 24) & 0xFF)
                    . chr(($tuple >> 16) & 0xFF)
                    . chr(($tuple >> 8) & 0xFF)
                    . chr($tuple & 0xFF);
                $tuple = 0;
                $count = 0;
            }
        }

        if ($count > 0) {
            // Final partial group: pad with 'u' (84), emit count-1 bytes.
            for ($i = $count; $i < 5; $i++) {
                $tuple = $tuple * 85 + 84;
            }
            for ($i = 0; $i < $count - 1; $i++) {
                $out .= chr(($tuple >> (24 - $i * 8)) & 0xFF);
            }
        }

        return $out;
    }

    /** RunLengthDecode (§7.4.5). */
    public static function runLength(string $data): string
    {
        $out = '';
        $len = strlen($data);
        $i = 0;
        while ($i < $len) {
            $length = ord($data[$i++]);
            if ($length === 128) {
                break; // EOD
            }
            if ($length < 128) {
                $n = $length + 1;
                $out .= substr($data, $i, $n);
                $i += $n;
            } else {
                $n = 257 - $length;
                if ($i < $len) {
                    $out .= str_repeat($data[$i], $n);
                    $i++;
                }
            }
        }
        return $out;
    }

    /** LZWDecode (§7.4.4.2), variable-width codes with EarlyChange support. */
    public static function lzw(string $data, int $earlyChange = 1): string
    {
        $out = '';
        $len = strlen($data);

        $bitBuffer = 0;
        $bitCount = 0;
        $codeWidth = 9;
        $pos = 0;

        $dict = self::lzwInitialTable();
        $next = 258;
        /** @var string|null $prev */
        $prev = null;

        while (true) {
            while ($bitCount < $codeWidth && $pos < $len) {
                $bitBuffer = ($bitBuffer << 8) | ord($data[$pos++]);
                $bitCount += 8;
            }
            if ($bitCount < $codeWidth) {
                break;
            }
            $code = ($bitBuffer >> ($bitCount - $codeWidth)) & ((1 << $codeWidth) - 1);
            $bitCount -= $codeWidth;

            if ($code === 257) {
                break; // EOD
            }
            if ($code === 256) {
                $dict = self::lzwInitialTable();
                $codeWidth = 9;
                $next = 258;
                $prev = null;
                continue;
            }

            if (isset($dict[$code])) {
                $entry = $dict[$code];
            } elseif ($code === $next && $prev !== null) {
                $entry = $prev . $prev[0];
            } else {
                throw new PdfParseException('LZWDecode: invalid code');
            }

            $out .= $entry;

            if ($prev !== null) {
                $dict[$next++] = $prev . $entry[0];
            }
            $prev = $entry;

            // Widen the code one step before the table fills when EarlyChange
            // is set (PDF/TIFF default 1): grow at next == 2^width - earlyChange.
            // Cap at the 12-bit LZW maximum.
            if ($next + $earlyChange >= (1 << $codeWidth)) {
                $codeWidth = min($codeWidth + 1, 12);
            }
        }

        return $out;
    }

    /**
     * Base LZW string table: single-byte entries 0–255 plus the clear (256)
     * and EOD (257) control markers.
     *
     * @return array<int,string>
     */
    private static function lzwInitialTable(): array
    {
        $dict = [];
        for ($i = 0; $i < 256; $i++) {
            $dict[$i] = chr($i);
        }
        $dict[256] = '';
        $dict[257] = '';
        return $dict;
    }

    /**
     * Reverse a PNG (predictors 10–15) or TIFF (predictor 2) predictor
     * (ISO 32000-1 §7.4.4.4). Predictor 1 (none) returns the data unchanged.
     *
     * PNG predictors are byte-oriented, so any bit depth works once the row
     * length and bytes-per-pixel are computed in bytes. TIFF predictor 2 is
     * sample-oriented and supported for 8- and 16-bit components.
     */
    public static function applyPredictor(
        string $data,
        int $predictor,
        int $colors,
        int $bitsPerComponent,
        int $columns,
    ): string {
        if ($predictor <= 1) {
            return $data;
        }

        $colors = max(1, $colors);
        $bitsPerPixel = $colors * $bitsPerComponent;
        $rowLen = intdiv($columns * $bitsPerPixel + 7, 8);  // bytes per row (ceil)
        $bpp = max(1, intdiv($bitsPerPixel + 7, 8));        // bytes per pixel (>= 1)

        if ($predictor === 2) {
            return self::tiffPredictor2($data, $colors, $bitsPerComponent, $columns, $rowLen);
        }
        return self::pngPredictor($data, $bpp, $rowLen);
    }

    private static function tiffPredictor2(string $data, int $colors, int $bpc, int $columns, int $rowLen): string
    {
        if ($bpc === 8) {
            return self::tiffPredictor2Bytes($data, $colors, $rowLen);
        }
        if ($bpc === 16) {
            return self::tiffPredictor2Words($data, $colors, $columns, $rowLen);
        }
        throw new PdfParseException(
            "TIFF predictor with BitsPerComponent={$bpc} is not supported"
        );
    }

    /** TIFF predictor 2 for 8-bit samples: add the same component of the pixel to the left. */
    private static function tiffPredictor2Bytes(string $data, int $colors, int $rowLen): string
    {
        $out = $data;
        $len = strlen($out);
        for ($row = 0; $row < $len; $row += $rowLen) {
            for ($i = $colors; $i < $rowLen && $row + $i < $len; $i++) {
                $out[$row + $i] = chr((ord($out[$row + $i]) + ord($out[$row + $i - $colors])) & 0xFF);
            }
        }
        return $out;
    }

    /** TIFF predictor 2 for 16-bit big-endian samples. */
    private static function tiffPredictor2Words(string $data, int $colors, int $columns, int $rowLen): string
    {
        $out = $data;
        $len = strlen($out);
        for ($row = 0; $row + $rowLen <= $len; $row += $rowLen) {
            // Sample index s within the row; each sample is 2 bytes.
            for ($s = $colors; $s < $columns * $colors; $s++) {
                $cur = $row + $s * 2;
                $prev = $cur - $colors * 2;
                if ($cur + 1 >= $len) {
                    break;
                }
                $val = ((ord($out[$cur]) << 8) | ord($out[$cur + 1]))
                    + ((ord($out[$prev]) << 8) | ord($out[$prev + 1]));
                $val &= 0xFFFF;
                $out[$cur] = chr($val >> 8);
                $out[$cur + 1] = chr($val & 0xFF);
            }
        }
        return $out;
    }

    private static function pngPredictor(string $data, int $bpp, int $rowLen): string
    {
        $stride = $rowLen + 1; // +1 filter-type byte per row
        $len = strlen($data);
        $out = '';
        $prevRow = str_repeat("\0", $rowLen);

        for ($p = 0; $p + 1 <= $len; $p += $stride) {
            $filterType = ord($data[$p]);
            $row = substr($data, $p + 1, $rowLen);
            if (strlen($row) < $rowLen) {
                $row = str_pad($row, $rowLen, "\0");
            }
            $decoded = '';
            for ($i = 0; $i < $rowLen; $i++) {
                $raw = ord($row[$i]);
                $a = $i >= $bpp ? ord($decoded[$i - $bpp]) : 0;   // left
                $b = ord($prevRow[$i]);                            // up
                $c = $i >= $bpp ? ord($prevRow[$i - $bpp]) : 0;    // up-left
                $val = match ($filterType) {
                    0 => $raw,
                    1 => $raw + $a,
                    2 => $raw + $b,
                    3 => $raw + intdiv($a + $b, 2),
                    4 => $raw + self::paeth($a, $b, $c),
                    default => throw new PdfParseException("Unknown PNG filter type {$filterType}"),
                };
                $decoded .= chr($val & 0xFF);
            }
            $out .= $decoded;
            $prevRow = $decoded;
        }

        return $out;
    }

    private static function paeth(int $a, int $b, int $c): int
    {
        $p = $a + $b - $c;
        $pa = abs($p - $a);
        $pb = abs($p - $b);
        $pc = abs($p - $c);
        if ($pa <= $pb && $pa <= $pc) {
            return $a;
        }
        return $pb <= $pc ? $b : $c;
    }
}
