<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf;

/**
 * Optional Content Group (PDF layer).
 *
 * ISO 32000-1 §8.11 — /Type /OCG dictionary with /Name. Layers can be
 * toggled on/off in the PDF reader UI (Acrobat → Layers panel).
 *
 * Use cases:
 *  - Watermarks toggleable per workflow stage.
 *  - Engineering drawings: dimensions / annotations / centerlines.
 *  - Multi-language overlays.
 *  - Print vs screen variants.
 *
 * Created via Document::addLayer(); referenced on pages through
 * Page::beginLayer()/endLayer().
 */
final class PdfLayer
{
    public function __construct(
        public readonly string $name,
        public bool $defaultVisible = true,
        public readonly string $intent = 'View',
    ) {
        if (! in_array($intent, ['View', 'Design'], true)) {
            throw new \InvalidArgumentException('Layer intent must be View or Design');
        }
    }

    /**
     * Build /Type /OCG dictionary body.
     */
    public function dictBody(): string
    {
        $name = strtr($this->name, ['\\' => '\\\\', '(' => '\\(', ')' => '\\)']);

        return sprintf(
            '<< /Type /OCG /Name (%s) /Intent /%s >>',
            $name,
            $this->intent,
        );
    }
}
