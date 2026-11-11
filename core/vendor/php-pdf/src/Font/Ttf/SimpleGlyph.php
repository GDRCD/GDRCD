<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * Simple glyph parser + serializer for variable font outlines.
 *
 * Per OpenType spec §5.3.3, simple glyph format:
 *
 *   int16 numberOfContours (>=0 for simple)
 *   int16 xMin, yMin, xMax, yMax
 *   uint16 endPtsOfContours[numberOfContours]
 *   uint16 instructionLength
 *   uint8 instructions[instructionLength]
 *   uint8 flags[]  (variable length, run-length encoded)
 *   xCoordinates[]  (delta from previous, signed; 1 or 2 bytes per flags)
 *   yCoordinates[]  (same)
 *
 * Flags (per point):
 *   bit 0 (0x01) ON_CURVE
 *   bit 1 (0x02) X_SHORT_VECTOR (1 byte) vs (2 bytes)
 *   bit 2 (0x04) Y_SHORT_VECTOR
 *   bit 3 (0x08) REPEAT (next byte = repeat count)
 *   bit 4 (0x10) X_IS_SAME_OR_POSITIVE (interpretation depends on X_SHORT)
 *   bit 5 (0x20) Y_IS_SAME_OR_POSITIVE
 *   bit 6 (0x40) OVERLAP_SIMPLE
 *
 * Composite glyphs (numberOfContours < 0) are NOT handled here — the
 * outline is returned as-is.
 */
final class SimpleGlyph
{
    public const FLAG_ON_CURVE = 0x01;

    public const FLAG_X_SHORT = 0x02;

    public const FLAG_Y_SHORT = 0x04;

    public const FLAG_REPEAT = 0x08;

    public const FLAG_X_SAME = 0x10;

    public const FLAG_Y_SAME = 0x20;

    public const FLAG_OVERLAP_SIMPLE = 0x40;

    /**
     * @param  list<int>  $endPts  end-of-contour point indices
     * @param  list<int>  $flags   one flag per point (after run-length expansion)
     * @param  list<int>  $xCoords absolute x per point
     * @param  list<int>  $yCoords absolute y per point
     */
    public function __construct(
        public readonly array $endPts,
        public readonly string $instructions,
        public readonly array $flags,
        public readonly array $xCoords,
        public readonly array $yCoords,
        public readonly int $xMin,
        public readonly int $yMin,
        public readonly int $xMax,
        public readonly int $yMax,
    ) {}

    /**
     * Parse simple glyph from raw bytes. Returns null if composite or empty.
     */
    public static function parse(string $bytes): ?self
    {
        if (strlen($bytes) < 10) {
            return null;
        }
        $numContours = self::s16($bytes, 0);
        if ($numContours <= 0) {
            return null; // composite (-1) or empty (0) — not handled here
        }
        $xMin = self::s16($bytes, 2);
        $yMin = self::s16($bytes, 4);
        $xMax = self::s16($bytes, 6);
        $yMax = self::s16($bytes, 8);

        $cursor = 10;
        $endPts = [];
        for ($i = 0; $i < $numContours; $i++) {
            $endPts[] = self::u16($bytes, $cursor);
            $cursor += 2;
        }
        $pointCount = $endPts[$numContours - 1] + 1;

        $instructionLength = self::u16($bytes, $cursor);
        $cursor += 2;
        $instructions = substr($bytes, $cursor, $instructionLength);
        $cursor += $instructionLength;

        // Decode flags (run-length encoded).
        $flags = [];
        while (count($flags) < $pointCount) {
            $f = ord($bytes[$cursor]);
            $cursor++;
            $flags[] = $f;
            if (($f & self::FLAG_REPEAT) !== 0) {
                $repeat = ord($bytes[$cursor]);
                $cursor++;
                for ($r = 0; $r < $repeat && count($flags) < $pointCount; $r++) {
                    $flags[] = $f;
                }
            }
        }

        // Decode x coords (deltas).
        $xCoords = [];
        $x = 0;
        foreach ($flags as $f) {
            if (($f & self::FLAG_X_SHORT) !== 0) {
                $byte = ord($bytes[$cursor]);
                $cursor++;
                $sign = ($f & self::FLAG_X_SAME) !== 0 ? 1 : -1;
                $x += $sign * $byte;
            } elseif (($f & self::FLAG_X_SAME) === 0) {
                // 2-byte signed delta
                $delta = self::s16($bytes, $cursor);
                $cursor += 2;
                $x += $delta;
            }
            // else: x unchanged (SAME bit set, SHORT bit clear → delta = 0)
            $xCoords[] = $x;
        }
        // Decode y coords.
        $yCoords = [];
        $y = 0;
        foreach ($flags as $f) {
            if (($f & self::FLAG_Y_SHORT) !== 0) {
                $byte = ord($bytes[$cursor]);
                $cursor++;
                $sign = ($f & self::FLAG_Y_SAME) !== 0 ? 1 : -1;
                $y += $sign * $byte;
            } elseif (($f & self::FLAG_Y_SAME) === 0) {
                $delta = self::s16($bytes, $cursor);
                $cursor += 2;
                $y += $delta;
            }
            $yCoords[] = $y;
        }

        return new self($endPts, $instructions, $flags, $xCoords, $yCoords,
            $xMin, $yMin, $xMax, $yMax);
    }

    /**
     * Serialize back to TTF simple glyph bytes. Recomputes bbox.
     *
     * @param  list<int>  $xCoords  override x coords (if applying deltas)
     * @param  list<int>  $yCoords  override y coords
     */
    public function serialize(?array $xCoords = null, ?array $yCoords = null): string
    {
        $xs = $xCoords ?? $this->xCoords;
        $ys = $yCoords ?? $this->yCoords;
        $n = count($xs);

        // Recompute bbox.
        $xMin = $xs ? min($xs) : 0;
        $yMin = $ys ? min($ys) : 0;
        $xMax = $xs ? max($xs) : 0;
        $yMax = $ys ? max($ys) : 0;

        // Header.
        $out = pack('n*', count($this->endPts))
            . pack('n*', $xMin & 0xFFFF, $yMin & 0xFFFF, $xMax & 0xFFFF, $yMax & 0xFFFF);
        foreach ($this->endPts as $ep) {
            $out .= pack('n', $ep);
        }
        $out .= pack('n', strlen($this->instructions)) . $this->instructions;

        // Encode flags (no run-length compression in this port for simplicity).
        // Also encode x/y delta bytes.
        $xPart = '';
        $yPart = '';
        $prevX = 0;
        $prevY = 0;
        $encodedFlags = [];
        for ($i = 0; $i < $n; $i++) {
            $dx = $xs[$i] - $prevX;
            $dy = $ys[$i] - $prevY;
            $prevX = $xs[$i];
            $prevY = $ys[$i];
            $f = $this->flags[$i] & (self::FLAG_ON_CURVE | self::FLAG_OVERLAP_SIMPLE);

            // X encoding.
            if ($dx === 0) {
                $f |= self::FLAG_X_SAME;
                // no bytes emitted
            } elseif ($dx >= -255 && $dx <= 255) {
                $f |= self::FLAG_X_SHORT;
                if ($dx >= 0) {
                    $f |= self::FLAG_X_SAME;
                    $xPart .= chr($dx);
                } else {
                    $xPart .= chr(-$dx);
                }
            } else {
                $xPart .= pack('n', $dx & 0xFFFF);
            }
            // Y encoding.
            if ($dy === 0) {
                $f |= self::FLAG_Y_SAME;
            } elseif ($dy >= -255 && $dy <= 255) {
                $f |= self::FLAG_Y_SHORT;
                if ($dy >= 0) {
                    $f |= self::FLAG_Y_SAME;
                    $yPart .= chr($dy);
                } else {
                    $yPart .= chr(-$dy);
                }
            } else {
                $yPart .= pack('n', $dy & 0xFFFF);
            }
            $encodedFlags[] = $f;
        }
        // Emit flags (1 byte per point; skipping REPEAT compression).
        foreach ($encodedFlags as $f) {
            $out .= chr($f);
        }
        $out .= $xPart . $yPart;

        return $out;
    }

    private static function u16(string $b, int $o): int
    {
        return (ord($b[$o]) << 8) | ord($b[$o + 1]);
    }

    private static function s16(string $b, int $o): int
    {
        $v = self::u16($b, $o);

        return $v >= 0x8000 ? $v - 0x10000 : $v;
    }
}
