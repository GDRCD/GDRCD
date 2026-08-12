<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * EAN-8 barcode encoder.
 *
 * EAN-8: 8 digit input (7 data + 1 check) per ISO/IEC 15420.
 *
 * Structure:
 *   [LeftQuiet 7 modules]
 *   [Start guard 101]
 *   [4 left digits — 7 modules each, all L-coded]
 *   [Center guard 01010]
 *   [4 right digits — 7 modules each, R-coded]
 *   [End guard 101]
 *   [RightQuiet 7 modules]
 *
 * Total = 67 modules + 14 quiet = 81.
 *
 * Unlike EAN-13, EAN-8 does NOT use L/G parity-shifting —
 * all left digits are always L-coded.
 */
final class Ean8Encoder
{
    /** L-code patterns (left half) — identical to EAN-13 L. */
    private const L = [
        '0001101', '0011001', '0010011', '0111101', '0100011',
        '0110001', '0101111', '0111011', '0110111', '0001011',
    ];

    /** R-code patterns (right half) — identical to EAN-13 R. */
    private const R = [
        '1110010', '1100110', '1101100', '1000010', '1011100',
        '1001110', '1010000', '1000100', '1001000', '1110100',
    ];

    private const START_GUARD = '101';

    private const CENTER_GUARD = '01010';

    private const END_GUARD = '101';

    /** @var list<bool> */
    private array $modules = [];

    /**
     * Full canonical 8-digit string (with computed checksum).
     */
    public readonly string $canonical;

    /**
     * @param  string  $digits  7 or 8 digits. If 7 — checksum is computed
     *                          automatically. If 8 — checksum is validated.
     */
    public function __construct(string $digits)
    {
        if (! preg_match('@^\d+$@', $digits)) {
            throw new \InvalidArgumentException('EAN-8 input must be digits only');
        }
        if (strlen($digits) === 7) {
            $digits .= (string) self::computeCheckDigit($digits);
        }
        if (strlen($digits) !== 8) {
            throw new \InvalidArgumentException('EAN-8 input must be 7 or 8 digits');
        }
        $expected = self::computeCheckDigit(substr($digits, 0, 7));
        if ((int) $digits[7] !== $expected) {
            throw new \InvalidArgumentException(sprintf(
                'EAN-8 checksum mismatch: expected %d, got %s',
                $expected,
                $digits[7],
            ));
        }

        $this->canonical = $digits;
        $this->encode($digits);
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
    public function modulesWithQuietZone(int $quietModules = 7): array
    {
        $quiet = array_fill(0, $quietModules, false);

        return [...$quiet, ...$this->modules, ...$quiet];
    }

    /**
     * Mod 10 weighted checksum for 7 input digits.
     *
     * EAN-8 (unlike EAN-13) gives weight 3 to the first digit:
     *   sum = d[0]*3 + d[1]*1 + d[2]*3 + d[3]*1 + d[4]*3 + d[5]*1 + d[6]*3
     *   check = (10 - sum % 10) % 10
     */
    public static function computeCheckDigit(string $first7): int
    {
        if (strlen($first7) !== 7 || ! preg_match('@^\d+$@', $first7)) {
            throw new \InvalidArgumentException('computeCheckDigit expects 7 digits');
        }
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $d = (int) $first7[$i];
            $sum += ($i % 2 === 0) ? $d * 3 : $d;
        }

        return (10 - $sum % 10) % 10;
    }

    private function encode(string $digits): void
    {
        $bits = self::START_GUARD;
        for ($i = 0; $i < 4; $i++) {
            $d = (int) $digits[$i];
            $bits .= self::L[$d];
        }
        $bits .= self::CENTER_GUARD;
        for ($i = 4; $i < 8; $i++) {
            $d = (int) $digits[$i];
            $bits .= self::R[$d];
        }
        $bits .= self::END_GUARD;

        $this->modules = array_map(fn (string $c): bool => $c === '1', str_split($bits));
    }
}
