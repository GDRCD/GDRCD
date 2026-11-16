<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * `avar` table parser — non-linear axis value mapping.
 *
 * Per OpenType spec §3.2: avar maps from "default" normalized space
 * (linear -1..+1 between min/default/max) to "actual" normalized space
 * used by variation data. Without avar, linear default normalization.
 *
 * Format v1.0:
 *   uint16 majorVersion (=1)
 *   uint16 minorVersion (=0 or 1)
 *   uint16 reserved
 *   uint16 axisCount
 *   SegmentMaps[axisCount]
 *
 * SegmentMaps:
 *   uint16 positionMapCount
 *   AxisValueMap[positionMapCount]:
 *     F2DOT14 fromCoordinate (default-normalized)
 *     F2DOT14 toCoordinate   (avar-normalized)
 *
 * Each axis's mapping is a piecewise-linear function. Mapping points must
 * include (-1,-1), (0,0), (1,1) endpoints.
 */
final class AvarReader
{
    /**
     * @param  list<list<array{from:float, to:float}>>  $segmentMaps
     */
    public function __construct(public readonly array $segmentMaps)
    {
    }

    /**
     * @param  array{offset:int, length:int}  $tableInfo
     */
    public static function read(string $bytes, array $tableInfo): self
    {
        $offset = $tableInfo['offset'];
        $axisCount = self::u16($bytes, $offset + 6);
        $maps = [];
        $cursor = $offset + 8;
        for ($a = 0; $a < $axisCount; $a++) {
            $pmCount = self::u16($bytes, $cursor);
            $cursor += 2;
            $pairs = [];
            for ($i = 0; $i < $pmCount; $i++) {
                $pairs[] = [
                    'from' => self::f2dot14($bytes, $cursor),
                    'to' => self::f2dot14($bytes, $cursor + 2),
                ];
                $cursor += 4;
            }
            $maps[] = $pairs;
        }

        return new self($maps);
    }

    /**
     * Apply avar mapping for axis $axisIdx to normalized coord $coord.
     * Piecewise-linear interpolation between mapping points.
     */
    public function map(int $axisIdx, float $coord): float
    {
        $pairs = $this->segmentMaps[$axisIdx] ?? null;
        if ($pairs === null || count($pairs) < 2) {
            return $coord;
        }
        // Find segment containing $coord. Pairs sorted by `from`.
        $n = count($pairs);
        if ($coord <= $pairs[0]['from']) {
            return $pairs[0]['to'];
        }
        if ($coord >= $pairs[$n - 1]['from']) {
            return $pairs[$n - 1]['to'];
        }
        for ($i = 1; $i < $n; $i++) {
            $prev = $pairs[$i - 1];
            $cur = $pairs[$i];
            if ($coord <= $cur['from']) {
                if ($cur['from'] === $prev['from']) {
                    return $cur['to'];
                }
                $t = ($coord - $prev['from']) / ($cur['from'] - $prev['from']);

                return $prev['to'] + $t * ($cur['to'] - $prev['to']);
            }
        }

        return $coord;
    }

    private static function u16(string $b, int $o): int
    {
        return (ord($b[$o]) << 8) | ord($b[$o + 1]);
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
