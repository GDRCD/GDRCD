<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * OpenType `fvar` table parser (variable fonts).
 *
 * Parses variation axes (tag, min/default/max value, nameID) and named
 * instances (subfamily nameID + coordinate vector + optional PS nameID).
 *
 * fvar layout per OpenType spec:
 *  Header (16 bytes):
 *   uint16 majorVersion (1)
 *   uint16 minorVersion (0)
 *   uint16 axesArrayOffset (typically 16)
 *   uint16 reserved (= 2)
 *   uint16 axisCount
 *   uint16 axisSize (typically 20)
 *   uint16 instanceCount
 *   uint16 instanceSize (4 + 4*axisCount OR 6 + 4*axisCount)
 *
 *  Each axis (axisSize bytes):
 *   Tag    axisTag (4 bytes)
 *   Fixed  minValue, defaultValue, maxValue (each 4 bytes, 16.16 signed)
 *   uint16 flags
 *   uint16 axisNameID
 *
 *  Each instance (instanceSize bytes):
 *   uint16 subfamilyNameID
 *   uint16 flags
 *   Fixed  coordinates[axisCount]
 *   uint16 postScriptNameID  (optional, present iff instanceSize ≥ 6+4*axisCount)
 */
final class FvarReader
{
    /**
     * @param  array{offset:int, length:int}  $tableInfo
     * @return array{
     *     axes: list<array{tag:string, min:float, default:float, max:float, nameId:int, flags:int}>,
     *     instances: list<array{nameId:int, coordinates:array<string, float>, postScriptNameId:?int, flags:int}>
     * }
     */
    public function read(string $bytes, array $tableInfo): array
    {
        $offset = $tableInfo['offset'];
        // Header
        $axesArrayOffset = self::readUInt16($bytes, $offset + 4);
        $axisCount = self::readUInt16($bytes, $offset + 8);
        $axisSize = self::readUInt16($bytes, $offset + 10);
        $instanceCount = self::readUInt16($bytes, $offset + 12);
        $instanceSize = self::readUInt16($bytes, $offset + 14);

        // Axes array
        $axes = [];
        $axisOffset = $offset + $axesArrayOffset;
        $axisTags = [];
        for ($i = 0; $i < $axisCount; $i++) {
            $base = $axisOffset + $i * $axisSize;
            $tag = substr($bytes, $base, 4);
            $axisTags[] = $tag;
            $axes[] = [
                'tag' => $tag,
                'min' => self::readFixed($bytes, $base + 4),
                'default' => self::readFixed($bytes, $base + 8),
                'max' => self::readFixed($bytes, $base + 12),
                'flags' => self::readUInt16($bytes, $base + 16),
                'nameId' => self::readUInt16($bytes, $base + 18),
            ];
        }

        // Instances array
        $instances = [];
        $instanceOffset = $axisOffset + $axisCount * $axisSize;
        $hasPsId = $instanceSize >= 6 + 4 * $axisCount;
        for ($i = 0; $i < $instanceCount; $i++) {
            $base = $instanceOffset + $i * $instanceSize;
            $subfamilyNameId = self::readUInt16($bytes, $base);
            $flags = self::readUInt16($bytes, $base + 2);
            $coords = [];
            for ($a = 0; $a < $axisCount; $a++) {
                $coords[$axisTags[$a]] = self::readFixed($bytes, $base + 4 + 4 * $a);
            }
            $psId = $hasPsId ? self::readUInt16($bytes, $base + 4 + 4 * $axisCount) : null;
            $instances[] = [
                'nameId' => $subfamilyNameId,
                'flags' => $flags,
                'coordinates' => $coords,
                'postScriptNameId' => $psId,
            ];
        }

        return ['axes' => $axes, 'instances' => $instances];
    }

    private static function readUInt16(string $bytes, int $offset): int
    {
        return (ord($bytes[$offset]) << 8) | ord($bytes[$offset + 1]);
    }

    /**
     * 16.16 signed fixed-point. High 16 bits = integer part (signed),
     * low 16 bits = fractional part (unsigned numerator over 65536).
     */
    private static function readFixed(string $bytes, int $offset): float
    {
        $int = (ord($bytes[$offset]) << 8) | ord($bytes[$offset + 1]);
        if ($int & 0x8000) {
            $int -= 0x10000; // sign extend
        }
        $frac = (ord($bytes[$offset + 2]) << 8) | ord($bytes[$offset + 3]);

        return $int + $frac / 65536.0;
    }
}
