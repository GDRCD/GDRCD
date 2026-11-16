<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * EAN-13 / UPC-A barcode encoder.
 *
 * EAN-13: 12 digit input + computed checksum (13th digit).
 * UPC-A: 11 digit input + computed checksum (12th digit); rendered
 * as EAN-13 with leading zero ("0" + UPC-A = valid EAN-13).
 *
 * Structure (EAN-13):
 *   [LeftQuiet 9 modules]
 *   [Start guard 101]
 *   [6 left digits — 7 modules each, encoded L or G by first-digit pattern]
 *   [Center guard 01010]
 *   [6 right digits — 7 modules each, R encoding]
 *   [End guard 101]
 *   [RightQuiet 9 modules]
 *
 * Total = 95 modules + 18 quiet = 113.
 *
 * ISO/IEC 15420.
 */
final class Ean13Encoder
{
    /** L-code patterns (left half, odd parity) — 7-module strings. */
    private const L = [
        '0001101', '0011001', '0010011', '0111101', '0100011',
        '0110001', '0101111', '0111011', '0110111', '0001011',
    ];

    /** G-code patterns (left half, even parity). */
    private const G = [
        '0100111', '0110011', '0011011', '0100001', '0011101',
        '0111001', '0000101', '0010001', '0001001', '0010111',
    ];

    /** R-code patterns (right half). */
    private const R = [
        '1110010', '1100110', '1101100', '1000010', '1011100',
        '1001110', '1010000', '1000100', '1001000', '1110100',
    ];

    /**
     * First-digit pattern table (which left-half digits use L vs G).
     * Index 0..9 = first digit; value — 6-char string of 'L'/'G'.
     */
    private const FIRST_DIGIT_PATTERN = [
        'LLLLLL', 'LLGLGG', 'LLGGLG', 'LLGGGL', 'LGLLGG',
        'LGGLLG', 'LGGGLL', 'LGLGLG', 'LGLGGL', 'LGGLGL',
    ];

    private const START_GUARD = '101';

    private const CENTER_GUARD = '01010';

    private const END_GUARD = '101';

    /** Add-on start guard (4 modules). */
    private const ADDON_START_GUARD = '1011';

    /** Add-on inter-character separator (2 modules). */
    private const ADDON_SEPARATOR = '01';

    /** Inter-barcode separator gap between main barcode and add-on (modules). */
    private const ADDON_GAP_MODULES = 9;

    /** 2-digit add-on parity table — indexed by (value mod 4). */
    private const ADDON2_PARITY = [
        'LL', 'LG', 'GL', 'GG',
    ];

    /** 5-digit add-on parity table — indexed by check digit. */
    private const ADDON5_PARITY = [
        'GGLLL', 'GLGLL', 'GLLGL', 'GLLLG', 'LGGLL',
        'LLGGL', 'LLLGG', 'LGLGL', 'LGLLG', 'LLGLG',
    ];

    /** @var list<bool> */
    private array $modules = [];

    /**
     * Full canonical 13-digit string (with computed checksum).
     */
    public readonly string $canonical;

    /**
     * Add-on supplement digits (2 or 5 digits), null if no add-on.
     */
    public readonly ?string $addOn;

    /**
     * @param  string  $digits  12 or 13 digits for EAN-13, or 11/12 for UPC-A.
     * @param  bool  $upcA  if true, input = 11/12 digits UPC-A → converted
     *                       to EAN-13 prepending '0'.
     * @param  string|null  $addOn  EAN-2 (2 digits) or EAN-5 (5 digits) supplement.
     *                       Common usage: ISBN price (EAN-5) or periodical issue (EAN-2).
     */
    public function __construct(string $digits, bool $upcA = false, ?string $addOn = null)
    {
        if (! preg_match('@^\d+$@', $digits)) {
            throw new \InvalidArgumentException('EAN-13/UPC-A input must be digits only');
        }
        if ($upcA) {
            if (strlen($digits) !== 11 && strlen($digits) !== 12) {
                throw new \InvalidArgumentException('UPC-A input must be 11 or 12 digits');
            }
            $digits = '0'.$digits;
        }
        if (strlen($digits) === 12) {
            $digits .= (string) self::computeCheckDigit($digits);
        }
        if (strlen($digits) !== 13) {
            throw new \InvalidArgumentException('EAN-13 input must be 12 or 13 digits');
        }
        // Validate checksum if 13-digit input.
        $expected = self::computeCheckDigit(substr($digits, 0, 12));
        if ((int) $digits[12] !== $expected) {
            throw new \InvalidArgumentException(sprintf(
                'EAN-13 checksum mismatch: expected %d, got %s',
                $expected,
                $digits[12],
            ));
        }

        if ($addOn !== null) {
            if (! preg_match('@^\d+$@', $addOn)) {
                throw new \InvalidArgumentException('EAN add-on must be digits only');
            }
            if (strlen($addOn) !== 2 && strlen($addOn) !== 5) {
                throw new \InvalidArgumentException(
                    'EAN add-on must be exactly 2 (EAN-2) or 5 (EAN-5) digits, got '.strlen($addOn),
                );
            }
        }

        $this->canonical = $digits;
        $this->addOn = $addOn;
        $this->encode($digits, $addOn);
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
    public function modulesWithQuietZone(int $quietModules = 9): array
    {
        $quiet = array_fill(0, $quietModules, false);

        return [...$quiet, ...$this->modules, ...$quiet];
    }

    /**
     * Mod 10 weighted checksum: sum(digits at odd positions × 1 + even × 3),
     * then check digit = (10 - sum % 10) % 10.
     */
    public static function computeCheckDigit(string $first12): int
    {
        if (strlen($first12) !== 12 || ! preg_match('@^\d+$@', $first12)) {
            throw new \InvalidArgumentException('computeCheckDigit expects 12 digits');
        }
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $d = (int) $first12[$i];
            $sum += ($i % 2 === 0) ? $d : $d * 3;
        }

        return (10 - $sum % 10) % 10;
    }

    private function encode(string $digits, ?string $addOn): void
    {
        $first = (int) $digits[0];
        $leftPattern = self::FIRST_DIGIT_PATTERN[$first];

        $bits = self::START_GUARD;
        for ($i = 0; $i < 6; $i++) {
            $d = (int) $digits[$i + 1];
            $bits .= $leftPattern[$i] === 'L' ? self::L[$d] : self::G[$d];
        }
        $bits .= self::CENTER_GUARD;
        for ($i = 7; $i < 13; $i++) {
            $d = (int) $digits[$i];
            $bits .= self::R[$d];
        }
        $bits .= self::END_GUARD;

        if ($addOn !== null) {
            $bits .= str_repeat('0', self::ADDON_GAP_MODULES);
            $bits .= strlen($addOn) === 2
                ? self::encodeAddOn2($addOn)
                : self::encodeAddOn5($addOn);
        }

        // Convert bit string to bool array.
        $this->modules = array_map(fn (string $c): bool => $c === '1', str_split($bits));
    }

    /**
     * Encode 2-digit add-on supplement.
     *
     * Structure: start-guard (1011) + digit1 + separator (01) + digit2.
     * Parity selection: (value % 4) → LL/LG/GL/GG.
     * Total width: 4 + 7 + 2 + 7 = 20 modules.
     */
    private static function encodeAddOn2(string $addOn): string
    {
        $value = (int) $addOn;
        $parity = self::ADDON2_PARITY[$value % 4];

        $bits = self::ADDON_START_GUARD;
        for ($i = 0; $i < 2; $i++) {
            $d = (int) $addOn[$i];
            $bits .= $parity[$i] === 'L' ? self::L[$d] : self::G[$d];
            if ($i < 1) {
                $bits .= self::ADDON_SEPARATOR;
            }
        }

        return $bits;
    }

    /**
     * Encode 5-digit add-on supplement.
     *
     * Check digit: (sum(digits at indexes 0,2,4) * 3 + sum(digits at 1,3) * 9) % 10.
     * Parity table indexed by check digit determines L/G per character.
     * Structure: start-guard (1011) + digit1 + sep + digit2 + sep + digit3 + sep + digit4 + sep + digit5.
     * Total width: 4 + 5*7 + 4*2 = 47 modules.
     */
    private static function encodeAddOn5(string $addOn): string
    {
        $check = self::computeAddOn5CheckDigit($addOn);
        $parity = self::ADDON5_PARITY[$check];

        $bits = self::ADDON_START_GUARD;
        for ($i = 0; $i < 5; $i++) {
            $d = (int) $addOn[$i];
            $bits .= $parity[$i] === 'L' ? self::L[$d] : self::G[$d];
            if ($i < 4) {
                $bits .= self::ADDON_SEPARATOR;
            }
        }

        return $bits;
    }

    /**
     * Compute 5-digit add-on parity check digit.
     *
     * Formula: (3 × sum_odd_positions + 9 × sum_even_positions) % 10,
     * where odd = positions 0,2,4 and even = positions 1,3.
     */
    public static function computeAddOn5CheckDigit(string $addOn): int
    {
        if (strlen($addOn) !== 5 || ! preg_match('@^\d+$@', $addOn)) {
            throw new \InvalidArgumentException('computeAddOn5CheckDigit expects 5 digits');
        }
        $sumOdd = (int) $addOn[0] + (int) $addOn[2] + (int) $addOn[4];
        $sumEven = (int) $addOn[1] + (int) $addOn[3];

        return ($sumOdd * 3 + $sumEven * 9) % 10;
    }
}
