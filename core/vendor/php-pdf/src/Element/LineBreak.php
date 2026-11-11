<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

/**
 * Hard line break inside a paragraph (HTML `<br>`).
 *
 * Distinct from soft-wrap line breaking — this is an explicit line ending
 * within the same paragraph.
 */
final readonly class LineBreak implements InlineElement
{
}
