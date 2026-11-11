<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Image;

use Dskripchenko\PhpPdf\Pdf\Writer;

/**
 * Image XObject for PDF embedding.
 *
 * Supports PNG and JPEG. For JPEG passes bytes through (PDF accepts
 * DCT-encoded streams natively); for PNG decodes IDAT and re-encodes
 * via Flate.
 *
 * SCOPE LIMITATIONS:
 *  - PNG color types: only 2 (RGB) and 6 (RGBA) with bit depth 8
 *  - JPEG: only baseline / extended sequential DCT (SOF0/SOF1/SOF2/SOF3)
 *  - PNG interlacing (Adam7): NOT supported
 *  - PNG palette (color type 3): NOT supported
 *  - Alpha channel in RGBA: ignored, rendered as RGB
 *  - ICC color profiles: skip
 *  - 16-bit channels: skip
 *
 * Usage:
 *   $img = PdfImage::fromPath('/tmp/photo.jpg');
 *   $imgRef = $img->registerWith($writer);
 *   // in content stream:
 *   q  w 0 0 h x y cm  /Im1 Do  Q
 */
final class PdfImage
{
    private ?int $objectId = null;

    public function __construct(
        public readonly int $widthPx,
        public readonly int $heightPx,
        public readonly string $filter,         // /FlateDecode or /DCTDecode
        public readonly string $colorSpace,     // /DeviceRGB or /DeviceGray
        public readonly int $bitsPerComponent,  // typically 8
        public readonly string $imageData,      // raw bytes for PDF stream
    ) {}

    public static function fromPath(string $path): self
    {
        if (! is_readable($path)) {
            throw new \InvalidArgumentException("Cannot read image: $path");
        }
        $bytes = (string) file_get_contents($path);

        return self::fromBytes($bytes);
    }

    public static function fromBytes(string $bytes): self
    {
        if (str_starts_with($bytes, "\xFF\xD8\xFF")) {
            return self::parseJpeg($bytes);
        }
        if (str_starts_with($bytes, "\x89PNG\r\n\x1A\n")) {
            return self::parsePng($bytes);
        }
        throw new \InvalidArgumentException('Unsupported image format (expected PNG or JPEG).');
    }

    public function registerWith(Writer $writer): int
    {
        if ($this->objectId !== null) {
            return $this->objectId;
        }
        $dict = sprintf(
            '<< /Type /XObject /Subtype /Image /Width %d /Height %d '
            .'/ColorSpace %s /BitsPerComponent %d /Filter %s /Length %d >>',
            $this->widthPx,
            $this->heightPx,
            $this->colorSpace,
            $this->bitsPerComponent,
            $this->filter,
            strlen($this->imageData),
        );
        $this->objectId = $writer->addObject(sprintf(
            "%s\nstream\n%s\nendstream",
            $dict, $this->imageData,
        ));

        return $this->objectId;
    }

    /**
     * Parses a JPEG file. PDF accepts DCT-encoded streams directly (without
     * decoding), so the raw bytes are simply wrapped.
     *
     * JPEG structure: SOI(FF D8) + segments + SOS + compressed
     * data + EOI(FF D9). Each segment marker = 0xFF + type byte +
     * 2-byte length (the length bytes themselves are included).
     *
     * For dimensions, look for SOF0/SOF1/SOF2/SOF3 marker (0xFFC0..0xFFC3):
     *   1 byte sample precision
     *   2 bytes height (big-endian)
     *   2 bytes width
     *   1 byte numComponents (1=gray, 3=RGB, 4=CMYK)
     */
    private static function parseJpeg(string $bytes): self
    {
        $len = strlen($bytes);
        $pos = 2; // skip SOI marker FF D8

        while ($pos < $len - 1) {
            if (ord($bytes[$pos]) !== 0xFF) {
                throw new \RuntimeException('Invalid JPEG: marker expected at pos '.$pos);
            }
            $marker = ord($bytes[$pos + 1]);
            $pos += 2;

            // Markers without payload (TEM, RSTn, etc.)
            if ($marker === 0xD8 || $marker === 0xD9 || ($marker >= 0xD0 && $marker <= 0xD7)) {
                continue;
            }
            // SOS — start of scan; dimensions should already have been in SOF.
            if ($marker === 0xDA) {
                break;
            }

            // Payload length (includes the length bytes themselves, not payload only).
            $segLen = (ord($bytes[$pos]) << 8) | ord($bytes[$pos + 1]);

            // SOF0..SOF3 — baseline / extended / progressive / lossless.
            if ($marker >= 0xC0 && $marker <= 0xC3) {
                $precision = ord($bytes[$pos + 2]);
                $height = (ord($bytes[$pos + 3]) << 8) | ord($bytes[$pos + 4]);
                $width = (ord($bytes[$pos + 5]) << 8) | ord($bytes[$pos + 6]);
                $components = ord($bytes[$pos + 7]);
                $colorSpace = match ($components) {
                    1 => '/DeviceGray',
                    3 => '/DeviceRGB',
                    4 => '/DeviceCMYK',
                    default => throw new \RuntimeException("Unsupported JPEG components: $components"),
                };

                return new self(
                    widthPx: $width,
                    heightPx: $height,
                    filter: '/DCTDecode',
                    colorSpace: $colorSpace,
                    bitsPerComponent: $precision,
                    imageData: $bytes,
                );
            }

            $pos += $segLen;
        }
        throw new \RuntimeException('JPEG SOF marker not found.');
    }

    /**
     * Parses PNG file. PNG = magic (8 bytes) + sequence of chunks.
     * Each chunk: 4-byte length + 4-byte type + data + 4-byte CRC.
     *
     * IHDR (first chunk): 13 bytes
     *   4 bytes width
     *   4 bytes height
     *   1 byte bit depth
     *   1 byte color type (2 = RGB, 6 = RGBA, 0 = gray, 3 = palette, 4 = gray+alpha)
     *   1 byte compression (always 0 = deflate)
     *   1 byte filter (always 0)
     *   1 byte interlace (0 = none, 1 = Adam7)
     *
     * IDAT chunks — zlib-deflated image data. Concatenated together
     * if multiple, then passed as a PDF Flate stream.
     *
     * PDF Flate decode requires DecodeParms with Predictor 15 (PNG
     * filters), Colors, BitsPerComponent, Columns — otherwise the reader
     * cannot decompress correctly.
     */
    private static function parsePng(string $bytes): self
    {
        $pos = 8; // skip magic
        $len = strlen($bytes);

        $width = 0;
        $height = 0;
        $colorType = 0;
        $bitDepth = 0;
        $interlace = 0;
        $idat = '';

        while ($pos < $len) {
            $chunkLen = (ord($bytes[$pos]) << 24)
                | (ord($bytes[$pos + 1]) << 16)
                | (ord($bytes[$pos + 2]) << 8)
                | ord($bytes[$pos + 3]);
            $type = substr($bytes, $pos + 4, 4);
            $data = substr($bytes, $pos + 8, $chunkLen);
            $pos += 8 + $chunkLen + 4; // length + type + data + crc

            if ($type === 'IHDR') {
                $width = (ord($data[0]) << 24) | (ord($data[1]) << 16)
                    | (ord($data[2]) << 8) | ord($data[3]);
                $height = (ord($data[4]) << 24) | (ord($data[5]) << 16)
                    | (ord($data[6]) << 8) | ord($data[7]);
                $bitDepth = ord($data[8]);
                $colorType = ord($data[9]);
                $interlace = ord($data[12]);
                if ($interlace !== 0) {
                    throw new \RuntimeException('Adam7 interlaced PNG not supported.');
                }
            } elseif ($type === 'IDAT') {
                $idat .= $data;
            } elseif ($type === 'IEND') {
                break;
            }
        }

        if ($bitDepth !== 8) {
            throw new \RuntimeException("PNG bit depth $bitDepth not supported (expected 8).");
        }

        // Color type → ColorSpace + components.
        [$colorSpace, $components] = match ($colorType) {
            2 => ['/DeviceRGB', 3],   // RGB
            0 => ['/DeviceGray', 1],  // Grayscale
            // Note: RGBA (6) and Gray+Alpha (4) are rendered as
            // RGB/Gray without alpha.
            6 => ['/DeviceRGB', 4],
            4 => ['/DeviceGray', 2],
            default => throw new \RuntimeException("PNG color type $colorType not supported."),
        };

        // PNG IDAT is zlib-compressed with PNG filters per scanline.
        // PDF Flate decode requires DecodeParms predictor 15 (PNG-style).
        // Simplified: re-encode without predictor. That means
        // decompress IDAT, strip PNG filter bytes, re-compress without filter.
        // For a 1×1 image this is overkill, but for multi-byte images
        // a real decoder is required.
        //
        // PNG data after inflate: each scanline starts with 1 filter
        // byte, then scanline.width × bytesPerPixel actual pixel bytes.
        // bytesPerPixel = components × (bitDepth/8) = components × 1.
        $inflated = @gzuncompress($idat);
        if ($inflated === false) {
            throw new \RuntimeException('Failed to inflate PNG IDAT.');
        }
        $rowBytes = $width * $components;
        $unfilteredRows = '';
        $prevRow = str_repeat("\x00", $rowBytes);
        for ($y = 0; $y < $height; $y++) {
            $filterByte = ord($inflated[$y * ($rowBytes + 1)]);
            $row = substr($inflated, $y * ($rowBytes + 1) + 1, $rowBytes);
            $unfilteredRow = self::applyPngFilter($filterByte, $row, $prevRow, $components);
            $unfilteredRows .= $unfilteredRow;
            $prevRow = $unfilteredRow;
        }

        // Strip alpha if RGBA → RGB.
        if ($colorType === 6) {
            $stripped = '';
            for ($i = 0; $i < strlen($unfilteredRows); $i += 4) {
                $stripped .= substr($unfilteredRows, $i, 3); // skip alpha
            }
            $unfilteredRows = $stripped;
            $components = 3;
        }
        if ($colorType === 4) {
            $stripped = '';
            for ($i = 0; $i < strlen($unfilteredRows); $i += 2) {
                $stripped .= $unfilteredRows[$i]; // skip alpha
            }
            $unfilteredRows = $stripped;
            $components = 1;
        }

        $pdfStream = gzcompress($unfilteredRows);

        return new self(
            widthPx: $width,
            heightPx: $height,
            filter: '/FlateDecode',
            colorSpace: $colorSpace,
            bitsPerComponent: 8,
            imageData: $pdfStream,
        );
    }

    /**
     * Apply PNG filter to a scanline. Filter byte (0..4):
     *   0 = None (passthrough)
     *   1 = Sub (x - left)
     *   2 = Up (x - above)
     *   3 = Average (x - (left + above) / 2)
     *   4 = Paeth (x - paeth predictor)
     *
     * Minimal implementation for several filter types — filter 0 (None)
     * is sufficient as libpng defaults to it for small images.
     */
    private static function applyPngFilter(int $filter, string $row, string $prev, int $components): string
    {
        if ($filter === 0) {
            return $row;
        }
        $len = strlen($row);
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $x = ord($row[$i]);
            $a = $i >= $components ? ord($out[$i - $components]) : 0;
            $b = ord($prev[$i]);
            $c = $i >= $components ? ord($prev[$i - $components]) : 0;

            $result = match ($filter) {
                1 => ($x + $a) & 0xFF,
                2 => ($x + $b) & 0xFF,
                3 => ($x + (int) (($a + $b) / 2)) & 0xFF,
                4 => ($x + self::paethPredictor($a, $b, $c)) & 0xFF,
                default => throw new \RuntimeException("Unknown PNG filter $filter"),
            };
            $out .= chr($result);
        }

        return $out;
    }

    private static function paethPredictor(int $a, int $b, int $c): int
    {
        $p = $a + $b - $c;
        $pa = abs($p - $a);
        $pb = abs($p - $b);
        $pc = abs($p - $c);
        if ($pa <= $pb && $pa <= $pc) {
            return $a;
        }
        if ($pb <= $pc) {
            return $b;
        }

        return $c;
    }
}
