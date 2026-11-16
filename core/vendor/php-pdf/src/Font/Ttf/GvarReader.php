<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * `gvar` table parser — Glyph Variations.
 *
 * Provides per-glyph point delta vectors for variable fonts. Each glyph
 * has zero or more "tuple variations" — collections of (x, y) deltas
 * applied at specific axis coordinate regions.
 *
 * Format v1.0 (per OpenType spec §11):
 *
 * gvar header:
 *   uint16  majorVersion (=1)
 *   uint16  minorVersion (=0)
 *   uint16  axisCount
 *   uint16  sharedTupleCount
 *   Offset32 sharedTuplesOffset (relative to gvar start)
 *   uint16  glyphCount
 *   uint16  flags (bit 0: longOffsets — uint32 vs uint16/2 offsets)
 *   Offset32 glyphVariationDataArrayOffset (relative to gvar start)
 *   Offset[glyphCount+1] glyphVariationDataOffsets (relative to array offset)
 *
 * Shared tuples: sharedTupleCount × axisCount × F2DOT14 normalized coords.
 *
 * Per-glyph variation data:
 *   uint16 tupleVariationCount (bit 12: SHARED_POINT_NUMBERS, low 12 bits: count)
 *   Offset16 dataOffset (where the packed point/delta data starts)
 *   TupleVariationHeader[count]
 *   packed data (point numbers + X deltas + Y deltas per tuple)
 *
 * IUP (Interpolation of Unreferenced Points) is NOT implemented here;
 * points without an explicit delta remain at the default position. This
 * yields correct results for fonts where ALL outline points have explicit
 * deltas (typical for well-designed variable fonts), but may cause visual
 * glitches otherwise.
 */
final class GvarReader
{
    public const FLAG_LONG_OFFSETS = 0x0001;

    public const TUPLE_EMBEDDED_PEAK = 0x8000;

    public const TUPLE_INTERMEDIATE_REGION = 0x4000;

    public const TUPLE_PRIVATE_POINT_NUMBERS = 0x2000;

    public const TUPLE_INDEX_MASK = 0x0FFF;

    public const TUPLES_SHARED_POINT_NUMBERS = 0x8000;

    public const TUPLES_COUNT_MASK = 0x0FFF;

    /**
     * @param  list<list<float>>  $sharedTuples  shared tuple coords (sharedTupleCount × axisCount)
     * @param  list<int>  $glyphDataOffsets  absolute byte offsets per glyph (length = glyphCount + 1; last is end sentinel)
     */
    public function __construct(
        public readonly int $axisCount,
        public readonly array $sharedTuples,
        public readonly array $glyphDataOffsets,
        private readonly string $bytes,
    ) {
    }

    /**
     * @param  array{offset:int, length:int}  $tableInfo
     */
    public static function read(string $bytes, array $tableInfo): self
    {
        $base = $tableInfo['offset'];
        $axisCount = self::u16($bytes, $base + 4);
        $sharedTupleCount = self::u16($bytes, $base + 6);
        $sharedTuplesOffset = self::u32($bytes, $base + 8);
        $glyphCount = self::u16($bytes, $base + 12);
        $flags = self::u16($bytes, $base + 14);
        $dataArrayOffset = self::u32($bytes, $base + 16);
        $longOffsets = ($flags & self::FLAG_LONG_OFFSETS) !== 0;

        // Read per-glyph data offsets (glyphCount + 1 entries, last = end sentinel).
        $offsets = [];
        $cursor = $base + 20;
        for ($i = 0; $i <= $glyphCount; $i++) {
            if ($longOffsets) {
                $offsets[] = $base + $dataArrayOffset + self::u32($bytes, $cursor);
                $cursor += 4;
            } else {
                $offsets[] = $base + $dataArrayOffset + self::u16($bytes, $cursor) * 2;
                $cursor += 2;
            }
        }

        // Read shared tuples.
        $sharedTuples = [];
        $sharedBase = $base + $sharedTuplesOffset;
        for ($i = 0; $i < $sharedTupleCount; $i++) {
            $tuple = [];
            for ($a = 0; $a < $axisCount; $a++) {
                $tuple[] = self::f2dot14($bytes, $sharedBase + ($i * $axisCount + $a) * 2);
            }
            $sharedTuples[] = $tuple;
        }

        return new self($axisCount, $sharedTuples, $offsets, $bytes);
    }

    /**
     * Compute per-point (x, y) deltas for a glyph under the given
     * normalized coords.
     *
     * Returns a sparse array: [pointIdx => ['x' => dx, 'y' => dy]].
     * Points without an explicit delta in any tuple are absent from the
     * result (NB: this is IUP-deferred — apply the default position).
     *
     * @param  array<int, float>  $normCoords  axis index → normalized -1..+1
     * @return array<int, array{x:float, y:float}>
     */
    public function glyphDeltas(int $glyphId, array $normCoords, int $glyphPointCount): array
    {
        if ($glyphId < 0 || $glyphId + 1 >= count($this->glyphDataOffsets)) {
            return [];
        }
        $start = $this->glyphDataOffsets[$glyphId];
        $end = $this->glyphDataOffsets[$glyphId + 1];
        if ($end - $start < 4) {
            return [];
        }

        // Per-glyph header
        $tupleHeader = self::u16($this->bytes, $start);
        $dataOffset = self::u16($this->bytes, $start + 2);
        $sharedPointNumbers = ($tupleHeader & self::TUPLES_SHARED_POINT_NUMBERS) !== 0;
        $tupleCount = $tupleHeader & self::TUPLES_COUNT_MASK;

        // Tuple variation headers start after the 4-byte glyph header.
        // Packed data (point numbers + deltas) starts at $start + $dataOffset.
        $headerCursor = $start + 4;
        $dataCursor = $start + $dataOffset;

        // Read shared point numbers if present.
        $sharedPoints = null;
        if ($sharedPointNumbers) {
            [$sharedPoints, $dataCursor] = self::readPackedPointNumbers($this->bytes, $dataCursor, $glyphPointCount);
        }

        $accumulated = [];
        for ($t = 0; $t < $tupleCount; $t++) {
            $dataSize = self::u16($this->bytes, $headerCursor);
            $tupleIndex = self::u16($this->bytes, $headerCursor + 2);
            $headerCursor += 4;

            $peakTuple = null;
            $intermediateStart = null;
            $intermediateEnd = null;

            if (($tupleIndex & self::TUPLE_EMBEDDED_PEAK) !== 0) {
                $peakTuple = [];
                for ($a = 0; $a < $this->axisCount; $a++) {
                    $peakTuple[] = self::f2dot14($this->bytes, $headerCursor);
                    $headerCursor += 2;
                }
            } else {
                $sharedIdx = $tupleIndex & self::TUPLE_INDEX_MASK;
                $peakTuple = $this->sharedTuples[$sharedIdx] ?? null;
            }
            if (($tupleIndex & self::TUPLE_INTERMEDIATE_REGION) !== 0) {
                $intermediateStart = [];
                $intermediateEnd = [];
                for ($a = 0; $a < $this->axisCount; $a++) {
                    $intermediateStart[] = self::f2dot14($this->bytes, $headerCursor);
                    $headerCursor += 2;
                }
                for ($a = 0; $a < $this->axisCount; $a++) {
                    $intermediateEnd[] = self::f2dot14($this->bytes, $headerCursor);
                    $headerCursor += 2;
                }
            }

            if ($peakTuple === null) {
                $dataCursor += $dataSize;

                continue;
            }

            // Compute tuple scalar.
            $scalar = self::tupleScalar($peakTuple, $intermediateStart, $intermediateEnd, $normCoords);
            if ($scalar === 0.0) {
                $dataCursor += $dataSize;

                continue;
            }

            // Decode point numbers + deltas from packed data.
            $tupleDataStart = $dataCursor;
            $cur = $dataCursor;
            $points = $sharedPoints;
            if (($tupleIndex & self::TUPLE_PRIVATE_POINT_NUMBERS) !== 0) {
                [$points, $cur] = self::readPackedPointNumbers($this->bytes, $cur, $glyphPointCount);
            }
            if ($points === null) {
                // Implicit: all glyph points
                $points = range(0, $glyphPointCount - 1);
            }

            $pointCount = count($points);
            [$xDeltas, $cur] = self::readPackedDeltas($this->bytes, $cur, $pointCount);
            [$yDeltas, $cur] = self::readPackedDeltas($this->bytes, $cur, $pointCount);

            foreach ($points as $i => $ptIdx) {
                $dx = ($xDeltas[$i] ?? 0) * $scalar;
                $dy = ($yDeltas[$i] ?? 0) * $scalar;
                if (! isset($accumulated[$ptIdx])) {
                    $accumulated[$ptIdx] = ['x' => 0.0, 'y' => 0.0];
                }
                $accumulated[$ptIdx]['x'] += $dx;
                $accumulated[$ptIdx]['y'] += $dy;
            }

            $dataCursor = $tupleDataStart + $dataSize;
        }

        return $accumulated;
    }

    /**
     * Compute scalar for tuple — region tent function.
     *
     * @param  list<float>  $peak
     * @param  list<float>|null  $start
     * @param  list<float>|null  $end
     * @param  array<int, float>  $coords
     */
    private static function tupleScalar(array $peak, ?array $start, ?array $end, array $coords): float
    {
        $scalar = 1.0;
        foreach ($peak as $axisIdx => $p) {
            $c = $coords[$axisIdx] ?? 0.0;
            if ($p === 0.0) {
                continue; // axis not involved
            }
            if ($start === null || $end === null) {
                // Default region: 0..peak or peak..0
                $s = $p > 0 ? 0.0 : $p;
                $e = $p > 0 ? $p : 0.0;
                // Adjust: at $p we have factor 1, at boundary 0.
                if ($c < $s || $c > $e) {
                    return 0.0;
                }
                if ($c === $p) {
                    continue;
                }
                if ($p > 0) {
                    $scalar *= $c / $p;
                } else {
                    $scalar *= $c / $p;
                }
            } else {
                $s = $start[$axisIdx];
                $e = $end[$axisIdx];
                if ($c < $s || $c > $e) {
                    return 0.0;
                }
                if ($c === $p) {
                    continue;
                }
                if ($c < $p) {
                    $scalar *= ($c - $s) / ($p - $s);
                } else {
                    $scalar *= ($e - $c) / ($e - $p);
                }
            }
        }

        return $scalar;
    }

    /**
     * Decode packed point numbers per spec §11.6.4.
     *
     * @return array{0: list<int>|null, 1: int}  [points|null (=all), nextCursor]
     */
    private static function readPackedPointNumbers(string $bytes, int $offset, int $glyphPointCount): array
    {
        $count = ord($bytes[$offset]);
        $offset++;
        if (($count & 0x80) !== 0) {
            $count = (($count & 0x7F) << 8) | ord($bytes[$offset]);
            $offset++;
        }
        if ($count === 0) {
            return [null, $offset]; // null = all glyph points
        }

        $points = [];
        $current = 0;
        while (count($points) < $count) {
            $control = ord($bytes[$offset]);
            $offset++;
            $runLength = ($control & 0x7F) + 1;
            $isWord = ($control & 0x80) !== 0;
            for ($i = 0; $i < $runLength && count($points) < $count; $i++) {
                if ($isWord) {
                    $diff = (ord($bytes[$offset]) << 8) | ord($bytes[$offset + 1]);
                    $offset += 2;
                } else {
                    $diff = ord($bytes[$offset]);
                    $offset++;
                }
                $current += $diff;
                $points[] = $current;
            }
        }

        return [$points, $offset];
    }

    /**
     * Decode packed deltas per spec §11.6.5.
     *
     * Control byte encoding:
     *   bit 7 (0x80): DELTAS_ARE_ZERO — zero deltas (no following data)
     *   bit 6 (0x40): DELTAS_ARE_WORDS — int16 deltas (else int8)
     *   bits 0-5: run length minus 1
     *
     * @return array{0: list<int>, 1: int}
     */
    private static function readPackedDeltas(string $bytes, int $offset, int $count): array
    {
        $deltas = [];
        while (count($deltas) < $count) {
            $control = ord($bytes[$offset]);
            $offset++;
            $runLength = ($control & 0x3F) + 1;
            $isZero = ($control & 0x80) !== 0;
            $isWord = ($control & 0x40) !== 0;
            for ($i = 0; $i < $runLength && count($deltas) < $count; $i++) {
                if ($isZero) {
                    $deltas[] = 0;
                } elseif ($isWord) {
                    $v = (ord($bytes[$offset]) << 8) | ord($bytes[$offset + 1]);
                    if ($v >= 0x8000) {
                        $v -= 0x10000;
                    }
                    $deltas[] = $v;
                    $offset += 2;
                } else {
                    $v = ord($bytes[$offset]);
                    if ($v >= 0x80) {
                        $v -= 0x100;
                    }
                    $deltas[] = $v;
                    $offset++;
                }
            }
        }

        return [$deltas, $offset];
    }

    private static function u16(string $b, int $o): int
    {
        return (ord($b[$o]) << 8) | ord($b[$o + 1]);
    }

    private static function u32(string $b, int $o): int
    {
        return (ord($b[$o]) << 24) | (ord($b[$o + 1]) << 16) | (ord($b[$o + 2]) << 8) | ord($b[$o + 3]);
    }

    private static function f2dot14(string $b, int $o): float
    {
        $raw = (ord($b[$o]) << 8) | ord($b[$o + 1]);
        if ($raw >= 0x8000) {
            $raw -= 0x10000;
        }

        return $raw / 16384.0;
    }
}
