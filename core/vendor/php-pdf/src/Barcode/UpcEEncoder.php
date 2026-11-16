<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * UPC-E barcode encoder.
 *
 * UPC-E — zero-suppressed UPC-A variant, ISO/IEC 15420. 8 digits total:
 *   [NumberSystem 0|1] [6 data digits — compressed from UPC-A] [check digit]
 *
 * Structure:
 *   [LeftQuiet 9 modules]
 *   [Start guard 101 — 3 mod]
 *   [6 data digits — L or G coded by parity pattern — 42 mod]
 *   [End guard 010101 — 6 mod]
 *   [RightQuiet 7 modules]
 *
 * Total = 51 modules + 16 quiet = 67.
 *
 * The $digits parameter accepts:
 *  - 6 digits — body only; NSD=$numberSystem prepended, check computed
 *  - 7 digits — NSD + 6 body; check computed
 *  - 8 digits — full UPC-E with check digit; check validated
 *
 * Check digit derived from expanded UPC-A (mod 10 weighted).
 */
final class UpcEEncoder
{
    /** L-code (odd parity) — identical to EAN-13. */
    private const L = [
        '0001101', '0011001', '0010011', '0111101', '0100011',
        '0110001', '0101111', '0111011', '0110111', '0001011',
    ];

    /** G-code (even parity) — identical to EAN-13. */
    private const G = [
        '0100111', '0110011', '0011011', '0100001', '0011101',
        '0111001', '0000101', '0010001', '0001001', '0010111',
    ];

    private const START_GUARD = '101';

    private const END_GUARD = '010101';

    /**
     * Parity pattern for number system 0 — indexed by check digit.
     * 'E' = Even (G-code), 'O' = Odd (L-code).
     * For NSD=1 the pattern is inverted (E↔O).
     */
    private const NSD_0_PARITY = [
        'EEEOOO', 'EEOEOO', 'EEOOEO', 'EEOOOE', 'EOEEOO',
        'EOOEEO', 'EOOOEE', 'EOEOEO', 'EOEOOE', 'EOOEOE',
    ];

    /** @var list<bool> */
    private array $modules = [];

    /** Full canonical 8-digit UPC-E. */
    public readonly string $canonical;

    /** Number system digit (0 or 1). */
    public readonly int $numberSystem;

    /** Expanded UPC-A form (12 digits). */
    public readonly string $upcA;

    public function __construct(string $digits, int $numberSystem = 0)
    {
        if (! preg_match('@^\d+$@', $digits)) {
            throw new \InvalidArgumentException('UPC-E input must be digits only');
        }
        if ($numberSystem !== 0 && $numberSystem !== 1) {
            throw new \InvalidArgumentException('UPC-E number system must be 0 or 1');
        }

        // Normalize to 8-digit canonical.
        if (strlen($digits) === 6) {
            $nsd = $numberSystem;
            $body = $digits;
            $upcA = self::expandToUpcA($nsd, $body);
            $check = self::upcACheckDigit($upcA);
            $canonical = $nsd.$body.$check;
        } elseif (strlen($digits) === 7) {
            $nsd = (int) $digits[0];
            if ($nsd !== 0 && $nsd !== 1) {
                throw new \InvalidArgumentException(
                    "UPC-E number system digit must be 0 or 1, got $nsd",
                );
            }
            $body = substr($digits, 1, 6);
            $upcA = self::expandToUpcA($nsd, $body);
            $check = self::upcACheckDigit($upcA);
            $canonical = $digits.$check;
            $numberSystem = $nsd;
        } elseif (strlen($digits) === 8) {
            $nsd = (int) $digits[0];
            if ($nsd !== 0 && $nsd !== 1) {
                throw new \InvalidArgumentException(
                    "UPC-E number system digit must be 0 or 1, got $nsd",
                );
            }
            $body = substr($digits, 1, 6);
            $upcA = self::expandToUpcA($nsd, $body);
            $expected = self::upcACheckDigit($upcA);
            if ((int) $digits[7] !== $expected) {
                throw new \InvalidArgumentException(sprintf(
                    'UPC-E checksum mismatch: expected %d, got %s',
                    $expected,
                    $digits[7],
                ));
            }
            $canonical = $digits;
            $numberSystem = $nsd;
        } else {
            throw new \InvalidArgumentException(
                'UPC-E input must be 6, 7, or 8 digits, got '.strlen($digits),
            );
        }

        $this->canonical = $canonical;
        $this->numberSystem = $numberSystem;
        $this->upcA = $upcA;
        $this->encode();
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
     * Expand UPC-E body (6 digits) + NSD to full UPC-A (12 digits without check).
     *
     * Per ISO/IEC 15420 §B.2.1:
     * D1 D2 D3 D4 D5 D6 — body digits.
     *  D6 = 0|1|2 → UPC-A = NSD D1 D2 D6 0000 D3 D4 D5
     *  D6 = 3     → UPC-A = NSD D1 D2 D3 00000 D4 D5
     *  D6 = 4     → UPC-A = NSD D1 D2 D3 D4 00000 D5
     *  D6 ≥ 5     → UPC-A = NSD D1 D2 D3 D4 D5 0000 D6
     *
     * Returns 11-digit UPC-A (without check digit).
     */
    private static function expandToUpcA(int $nsd, string $body): string
    {
        $d6 = (int) $body[5];
        $d1 = $body[0];
        $d2 = $body[1];
        $d3 = $body[2];
        $d4 = $body[3];
        $d5 = $body[4];

        if ($d6 <= 2) {
            return $nsd.$d1.$d2.$body[5].'0000'.$d3.$d4.$d5;
        }
        if ($d6 === 3) {
            return $nsd.$d1.$d2.$d3.'00000'.$d4.$d5;
        }
        if ($d6 === 4) {
            return $nsd.$d1.$d2.$d3.$d4.'00000'.$d5;
        }

        // 5..9.
        return $nsd.$d1.$d2.$d3.$d4.$d5.'0000'.$body[5];
    }

    /**
     * UPC-A mod 10 weighted check digit (12-digit total: 11 input + 1 check).
     *
     * Weights: 3,1,3,1,3,1,3,1,3,1,3 — position 0 weight 3.
     */
    private static function upcACheckDigit(string $first11): int
    {
        if (strlen($first11) !== 11 || ! preg_match('@^\d+$@', $first11)) {
            throw new \InvalidArgumentException('upcACheckDigit expects 11 digits');
        }
        $sum = 0;
        for ($i = 0; $i < 11; $i++) {
            $d = (int) $first11[$i];
            $sum += ($i % 2 === 0) ? $d * 3 : $d;
        }

        return (10 - $sum % 10) % 10;
    }

    /**
     * Compute UPC-E check digit for 6-digit body + NSD.
     */
    public static function computeCheckDigit(string $body, int $numberSystem = 0): int
    {
        if (strlen($body) !== 6 || ! preg_match('@^\d+$@', $body)) {
            throw new \InvalidArgumentException('computeCheckDigit expects 6-digit body');
        }
        if ($numberSystem !== 0 && $numberSystem !== 1) {
            throw new \InvalidArgumentException('numberSystem must be 0 or 1');
        }

        return self::upcACheckDigit(self::expandToUpcA($numberSystem, $body));
    }

    private function encode(): void
    {
        $body = substr($this->canonical, 1, 6);
        $check = (int) $this->canonical[7];
        $pattern = self::NSD_0_PARITY[$check];

        // For NSD=1 invert pattern (swap E↔O).
        if ($this->numberSystem === 1) {
            $pattern = strtr($pattern, ['E' => 'O', 'O' => 'E']);
        }

        $bits = self::START_GUARD;
        for ($i = 0; $i < 6; $i++) {
            $d = (int) $body[$i];
            $bits .= $pattern[$i] === 'O' ? self::L[$d] : self::G[$d];
        }
        $bits .= self::END_GUARD;

        $this->modules = array_map(fn (string $c): bool => $c === '1', str_split($bits));
    }
}
