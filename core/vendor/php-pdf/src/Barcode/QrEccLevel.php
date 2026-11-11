<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Barcode;

/**
 * QR error correction levels.
 *
 * L = ~7% recovery (max data capacity)
 * M = ~15% recovery
 * Q = ~25% recovery
 * H = ~30% recovery (lowest data capacity, highest resilience)
 *
 * Format info bits in QR matrix encoded as:
 *   L = 01, M = 00, Q = 11, H = 10.
 */
enum QrEccLevel: string
{
    case L = 'L';
    case M = 'M';
    case Q = 'Q';
    case H = 'H';

    /**
     * 2-bit format identifier.
     */
    public function formatBits(): int
    {
        return match ($this) {
            self::L => 0b01,
            self::M => 0b00,
            self::Q => 0b11,
            self::H => 0b10,
        };
    }
}
