<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font\Ttf;

/**
 * Composite glyph parser + serializer.
 *
 * Composite glyph (numberOfContours < 0) — a combination of other glyphs
 * with optional position offsets + scale matrices. Used for diacritics
 * (e.g., letter with accent = base glyph + accent component).
 *
 * Format (OpenType §6.2):
 *   int16 numberOfContours (= -1 → composite)
 *   int16 xMin, yMin, xMax, yMax (bounding box)
 *   Per component:
 *     uint16 flags
 *     uint16 glyphIndex
 *     if ARG_1_AND_2_ARE_WORDS: int16 arg1, int16 arg2
 *     else: int8 arg1, int8 arg2
 *     if WE_HAVE_A_SCALE: F2DOT14 scale
 *     else if WE_HAVE_AN_X_AND_Y_SCALE: F2DOT14 xscale, yscale
 *     else if WE_HAVE_A_TWO_BY_TWO: F2DOT14 xscale, scale01, scale10, yscale
 *   Continue while flags has MORE_COMPONENTS set.
 *   If WE_HAVE_INSTRUCTIONS on the last component:
 *     uint16 numInstr, uint8 instructions[]
 *
 * Variation: gvar deltas apply to component anchor points (arg1, arg2 when
 * ARGS_ARE_XY_VALUES). Point N in gvar = component N's anchor.
 */
final class CompositeGlyph
{
    public const FLAG_ARG_1_AND_2_ARE_WORDS = 0x0001;
    public const FLAG_ARGS_ARE_XY_VALUES = 0x0002;
    public const FLAG_ROUND_XY_TO_GRID = 0x0004;
    public const FLAG_WE_HAVE_A_SCALE = 0x0008;
    public const FLAG_MORE_COMPONENTS = 0x0020;
    public const FLAG_WE_HAVE_AN_X_AND_Y_SCALE = 0x0040;
    public const FLAG_WE_HAVE_A_TWO_BY_TWO = 0x0080;
    public const FLAG_WE_HAVE_INSTRUCTIONS = 0x0100;
    public const FLAG_USE_MY_METRICS = 0x0200;
    public const FLAG_OVERLAP_COMPOUND = 0x0400;

    /**
     * @param  list<array{flags: int, glyphIndex: int, arg1: int, arg2: int, byteOffset: int, argSize: int, isXY: bool}>  $components
     * @param  string  $rawHeader  bytes 0..9 (5 int16: numContours + bbox)
     * @param  string  $rawTail    bytes after last component (instructions if present)
     */
    private function __construct(
        public readonly string $rawHeader,
        public readonly array $components,
        public readonly string $rawTail,
        public readonly string $originalBytes,
    ) {}

    /**
     * Parse composite glyph bytes. Returns null if glyph is not composite
     * (numberOfContours >= 0) or empty.
     */
    public static function parse(string $bytes): ?self
    {
        if (strlen($bytes) < 10) {
            return null;
        }
        $numContours = self::readInt16($bytes, 0);
        if ($numContours >= 0) {
            return null; // simple glyph
        }
        // Header: numContours + xMin + yMin + xMax + yMax = 10 bytes.
        $rawHeader = substr($bytes, 0, 10);
        $offset = 10;
        $components = [];
        $hasInstructions = false;
        do {
            $flags = self::readUint16($bytes, $offset);
            $compStart = $offset;
            $offset += 2;
            $glyphIndex = self::readUint16($bytes, $offset);
            $offset += 2;
            $argSize = ($flags & self::FLAG_ARG_1_AND_2_ARE_WORDS) ? 2 : 1;
            $argByteOffset = $offset;
            if ($argSize === 2) {
                $arg1 = self::readInt16($bytes, $offset);
                $arg2 = self::readInt16($bytes, $offset + 2);
            } else {
                $arg1 = self::readInt8($bytes, $offset);
                $arg2 = self::readInt8($bytes, $offset + 1);
            }
            $offset += $argSize * 2;
            // Skip optional transform.
            if ($flags & self::FLAG_WE_HAVE_A_SCALE) {
                $offset += 2;
            } elseif ($flags & self::FLAG_WE_HAVE_AN_X_AND_Y_SCALE) {
                $offset += 4;
            } elseif ($flags & self::FLAG_WE_HAVE_A_TWO_BY_TWO) {
                $offset += 8;
            }
            $components[] = [
                'flags' => $flags,
                'glyphIndex' => $glyphIndex,
                'arg1' => $arg1,
                'arg2' => $arg2,
                'byteOffset' => $argByteOffset,
                'argSize' => $argSize,
                'isXY' => (bool) ($flags & self::FLAG_ARGS_ARE_XY_VALUES),
                'componentStart' => $compStart,
                'componentEnd' => $offset,
            ];
            if ($flags & self::FLAG_WE_HAVE_INSTRUCTIONS) {
                $hasInstructions = true;
            }
        } while ($flags & self::FLAG_MORE_COMPONENTS);

        // Tail = instructions (if any) + padding.
        $rawTail = substr($bytes, $offset);

        return new self($rawHeader, $components, $rawTail, $bytes);
    }

    /**
     * Re-serialize composite glyph with modified component dx/dy offsets.
     *
     * @param  array<int, array{dx: int, dy: int}>  $newOffsets  componentIdx → new anchor offset
     */
    public function serialize(array $newOffsets): string
    {
        $out = $this->rawHeader;
        foreach ($this->components as $idx => $comp) {
            // Append flags + glyphIndex (unchanged).
            $out .= substr($this->originalBytes, $comp['componentStart'], 4);
            // Write potentially modified args.
            $arg1 = $comp['arg1'];
            $arg2 = $comp['arg2'];
            if (isset($newOffsets[$idx]) && $comp['isXY']) {
                $arg1 = $newOffsets[$idx]['dx'];
                $arg2 = $newOffsets[$idx]['dy'];
            }
            // Determine if args need promotion to int16 (when value is out of int8 range).
            $promoteToWords = $comp['argSize'] === 1 && (
                $arg1 < -128 || $arg1 > 127 || $arg2 < -128 || $arg2 > 127
            );
            if ($comp['argSize'] === 2 || $promoteToWords) {
                $out .= pack('n', $arg1 & 0xFFFF).pack('n', $arg2 & 0xFFFF);
                if ($promoteToWords) {
                    // Set FLAG_ARG_1_AND_2_ARE_WORDS bit in flags (already written above).
                    // Re-encode flags byte: read last 2 written bytes of $out and replace flags.
                    $flagsPos = strlen($out) - 4 - 2 - 2; // 2 bytes flags + 2 bytes glyphIndex + 2 args (4)
                    $newFlags = $comp['flags'] | self::FLAG_ARG_1_AND_2_ARE_WORDS;
                    $out = substr($out, 0, $flagsPos).pack('n', $newFlags).substr($out, $flagsPos + 2);
                }
            } else {
                $out .= pack('c', $arg1).pack('c', $arg2);
            }
            // Append transform bytes (unchanged), if any.
            $transformStart = $comp['byteOffset'] + $comp['argSize'] * 2;
            $transformEnd = $comp['componentEnd'];
            $out .= substr($this->originalBytes, $transformStart, $transformEnd - $transformStart);
        }
        // Append tail (instructions).
        $out .= $this->rawTail;

        return $out;
    }

    private static function readUint16(string $bytes, int $offset): int
    {
        return unpack('n', substr($bytes, $offset, 2))[1];
    }

    private static function readInt16(string $bytes, int $offset): int
    {
        $v = self::readUint16($bytes, $offset);

        return $v >= 0x8000 ? $v - 0x10000 : $v;
    }

    private static function readInt8(string $bytes, int $offset): int
    {
        $v = ord($bytes[$offset]);

        return $v >= 0x80 ? $v - 0x100 : $v;
    }
}
