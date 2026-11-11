<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font;

use Dskripchenko\PhpPdf\Font\Ttf\TtfFile;

/**
 * Composite of several FontProviders — tries each in order and returns
 * the first non-null result.
 *
 * Used when the caller has several font sources:
 *  - Bundled (LiberationFontProvider from php-pdf-fonts-liberation)
 *  - User-provided (DirectoryFontProvider with /var/fonts)
 *  - Fallback to dummy/null
 *
 * Providers are stored in insertion order — earliest wins.
 */
final class ChainedFontProvider implements FontProvider
{
    /** @var list<FontProvider> */
    private array $providers;

    public function __construct(FontProvider ...$providers)
    {
        $this->providers = array_values($providers);
    }

    /**
     * Appends a provider to the end of the chain (lowest priority).
     */
    public function append(FontProvider $provider): self
    {
        $this->providers[] = $provider;

        return $this;
    }

    /**
     * Prepends a provider to the start of the chain (highest priority).
     */
    public function prepend(FontProvider $provider): self
    {
        array_unshift($this->providers, $provider);

        return $this;
    }

    public function resolve(string $fontName): ?TtfFile
    {
        foreach ($this->providers as $provider) {
            $ttf = $provider->resolve($fontName);
            if ($ttf !== null) {
                return $ttf;
            }
        }

        return null;
    }

    /**
     * @return list<FontProvider>
     */
    public function providers(): array
    {
        return $this->providers;
    }
}
