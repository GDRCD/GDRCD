<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font;

use Dskripchenko\PhpPdf\Font\Ttf\TtfFile;

/**
 * Abstraction for font resolution: the caller asks for a font by name and
 * the implementation returns a TtfFile (or null if it does not know the name).
 *
 * Usage:
 *
 *   $provider = new ChainedFontProvider(
 *       new LiberationFontProvider(),     // from php-pdf-fonts-liberation
 *       new CustomFontProvider('/path/to/my/fonts'),
 *   );
 *
 *   $ttf = $provider->resolve('Arial');
 *   // → LiberationSans (metric-compatible aliasing in LiberationFontProvider)
 *
 *   $ttf = $provider->resolve('Helvetica');
 *   // → null (Helvetica = PDF base-14, embedding not required)
 *
 * Minimum API:
 *   - resolve(name): ?TtfFile
 *
 * Possible future expansions:
 *   - resolveBold/Italic variants
 *   - FontProvider implementations for CSS-family stacks
 *     ('Arial, Helvetica, sans-serif')
 *   - FontProviders for system-installed font discovery
 */
interface FontProvider
{
    /**
     * Resolves font name → TtfFile, or null if the provider does not know
     * the name.
     *
     * Names are case-sensitive — providers may normalize on their own.
     * PostScript names are the most stable variant (LiberationSans
     * vs "Liberation Sans" or "liberation-sans").
     */
    public function resolve(string $fontName): ?TtfFile;
}
