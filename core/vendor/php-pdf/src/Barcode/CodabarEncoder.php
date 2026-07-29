<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * Codabar (NW-7 / USS Codabar) barcode encoder.
 *
 * Used by libraries, blood banks, FedEx ground tracking, photo finishing
 * labs. Numeric + 6 punctuation + 4 start/stop characters (A/B/C/D).
 *
 * Encoding:
 *  - Data set: 0-9, `-`, `$`, `:`, `/`, `.`, `+` (16 chars)
 *  - Start/stop: A, B, C, D (4 options — typically A/A used)
 *  - Each character = 7 elements (4 bars + 3 spaces), 2 or 3 wide
 *  - Wide:narrow ratio = 2:1 (standard)
 *  - 1-narrow inter-character gap
 *
 * Module count per character:
 *  - 2-wide chars: 5 narrow + 2 wide = 5·1 + 2·2 = 9 modules
 *  - 3-wide chars: 4 narrow + 3 wide = 4·1 + 3·2 = 10 modules
 *
 * Not self-checking nor self-decoding; optional Mod-16 check digit can be
 * added externally if downstream parser requires it.
 */
final class CodabarEncoder
{
    /**
     * Pattern: char → 7-bit string where 1 = wide element, 0 = narrow.
     * Position 0 = first bar; alternates bar/space.
     */
    private const PATTERNS = [
        '0' => '0000011',
        '1' => '0000110',
        '2' => '0001001',
        '3' => '1100000',
        '4' => '0010010',
        '5' => '1000010',
        '6' => '0100001',
        '7' => '0100100',
        '8' => '0110000',
        '9' => '1001000',
        '-' => '0001100',
        '$' => '0011000',
        ':' => '1000101',
        '/' => '1010001',
        '.' => '1010100',
        '+' => '0010101',
        'A' => '0011010',
        'B' => '0101001',
        'C' => '0001011',
        'D' => '0001110',
    ];

    private const START_STOP_CHARS = ['A', 'B', 'C', 'D'];

    private const WIDE_RATIO = 2;

    /** @var list<bool> */
    private array $modules = [];

    /** Full encoded value (start + data + stop). */
    public readonly string $canonical;

    /**
     * @param  string  $data   Data characters (no start/stop wrapper);
     *                         case-insensitive — auto-uppercased.
     * @param  string  $start  Start delimiter (one of A, B, C, D). Default 'A'.
     * @param  string  $stop   Stop delimiter (one of A, B, C, D). Default 'A'.
     */
    public function __construct(string $data, string $start = 'A', string $stop = 'A')
    {
        $data = strtoupper($data);
        $start = strtoupper($start);
        $stop = strtoupper($stop);

        if ($data === '') {
            throw new \InvalidArgumentException('Codabar data must be non-empty');
        }
        if (! in_array($start, self::START_STOP_CHARS, true)) {
            throw new \InvalidArgumentException("Codabar start char must be one of A/B/C/D, got '$start'");
        }
        if (! in_array($stop, self::START_STOP_CHARS, true)) {
            throw new \InvalidArgumentException("Codabar stop char must be one of A/B/C/D, got '$stop'");
        }
        for ($i = 0; $i < strlen($data); $i++) {
            $c = $data[$i];
            if (in_array($c, self::START_STOP_CHARS, true)) {
                throw new \InvalidArgumentException(
                    "Codabar data may not contain start/stop char '$c' — use start/stop params instead",
                );
            }
            if (! isset(self::PATTERNS[$c])) {
                throw new \InvalidArgumentException(
                    "Codabar cannot encode character '$c' (0x".dechex(ord($c)).')',
                );
            }
        }

        $this->canonical = $start.$data.$stop;
        $this->encode($this->canonical);
    }

    /**
     * @return list<bool>
     */
    public function modules(): array
    {
        return $this->modules;
    }

    public function moduleCount(): int
    {
        return count($this->modules);
    }

    /**
     * @return list<bool>
     */
    public function modulesWithQuietZone(int $quietModules = 10): array
    {
        $quiet = array_fill(0, $quietModules, false);

        return [...$quiet, ...$this->modules, ...$quiet];
    }

    private function encode(string $value): void
    {
        $bits = '';
        $len = strlen($value);

        for ($i = 0; $i < $len; $i++) {
            $pattern = self::PATTERNS[$value[$i]];
            for ($j = 0; $j < 7; $j++) {
                $isBar = ($j % 2 === 0);
                $width = $pattern[$j] === '1' ? self::WIDE_RATIO : 1;
                $bits .= str_repeat($isBar ? '1' : '0', $width);
            }
            // Inter-character gap (1 narrow space) — except after last char.
            if ($i < $len - 1) {
                $bits .= '0';
            }
        }

        $this->modules = array_map(fn (string $c): bool => $c === '1', str_split($bits));
    }
}
