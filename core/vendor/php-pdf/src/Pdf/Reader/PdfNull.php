<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * The PDF `null` object (ISO 32000-1 §7.3.9).
 *
 * A distinct singleton so parsed values can carry an explicit null without
 * colliding with PHP `null` (which the resolver uses to mean "not found").
 */
final class PdfNull
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }
}
