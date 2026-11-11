<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Font;

use Dskripchenko\PhpPdf\Font\Ttf\TtfFile;

/**
 * FontProvider that scans a directory for TTF files and resolves them
 * by PostScript name (from the TTF name table).
 *
 * Lazy: TTFs are not parsed at construction — only when the first
 * resolve() call happens. Cached after the first parse.
 *
 * Usage:
 *   $provider = new DirectoryFontProvider('/path/to/fonts');
 *   $ttf = $provider->resolve('MyCustomFont-Regular');
 *
 * Recursive search is optional (default off).
 */
final class DirectoryFontProvider implements FontProvider
{
    /** @var array<string, string>|null  postscriptName → absolute path; lazily populated */
    private ?array $indexed = null;

    /** @var array<string, TtfFile>  cache parsed TtfFile instances */
    private array $cache = [];

    public function __construct(
        private readonly string $directoryPath,
        private readonly bool $recursive = false,
    ) {}

    public function resolve(string $fontName): ?TtfFile
    {
        if (isset($this->cache[$fontName])) {
            return $this->cache[$fontName];
        }
        $this->ensureIndexed();
        if (! isset($this->indexed[$fontName])) {
            return null;
        }

        return $this->cache[$fontName] = TtfFile::fromFile($this->indexed[$fontName]);
    }

    /**
     * Path to the resolved TTF without parsing (for debug / introspection).
     */
    public function pathFor(string $fontName): ?string
    {
        $this->ensureIndexed();

        return $this->indexed[$fontName] ?? null;
    }

    /**
     * All known fonts in the directory (after indexing).
     *
     * @return array<string, string>  postscriptName → path
     */
    public function knownFonts(): array
    {
        $this->ensureIndexed();

        return $this->indexed;
    }

    private function ensureIndexed(): void
    {
        if ($this->indexed !== null) {
            return;
        }
        $this->indexed = [];
        if (! is_dir($this->directoryPath)) {
            return;
        }
        $this->scanDirectory($this->directoryPath);
    }

    private function scanDirectory(string $dir): void
    {
        $items = scandir($dir);
        if ($items === false) {
            return;
        }
        foreach ($items as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir.'/'.$entry;
            if (is_dir($path)) {
                if ($this->recursive) {
                    $this->scanDirectory($path);
                }

                continue;
            }
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ($ext !== 'ttf' && $ext !== 'otf') {
                continue;
            }
            // Parse name from TTF.
            try {
                $ttf = TtfFile::fromFile($path);
                $name = $ttf->postScriptName();
                $this->indexed[$name] = $path;
                // Caching: the index stores the path, not the TtfFile. This
                // allows memory to be freed when TtfFile.bytes is not needed
                // immediately. resolve() loads the TtfFile lazily on first use.
            } catch (\Throwable) {
                // Skip unreadable / corrupt files.
            }
        }
    }
}
