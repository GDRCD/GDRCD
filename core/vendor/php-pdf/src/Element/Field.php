<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

use Dskripchenko\PhpPdf\Style\RunStyle;

/**
 * Dynamic content placeholder resolved by the layout engine.
 *
 * Supported kinds:
 *  - PAGE       — current page number
 *  - NUMPAGES   — total page count
 *  - DATE       — current date (format string after the colon)
 *  - TIME       — current time
 *  - MERGEFIELD — mail-merge placeholder
 *
 * Instruction format is `KIND` or `KIND:format-spec`:
 *   `PAGE`, `NUMPAGES`, `DATE:dd.MM.yyyy`, `MERGEFIELD:CustomerName`.
 *
 * PAGE/NUMPAGES resolve to numbers, DATE/TIME format the current
 * timestamp, MERGEFIELD is left as a placeholder for downstream
 * substitution.
 */
final readonly class Field implements InlineElement
{
    public const PAGE = 'PAGE';

    public const NUMPAGES = 'NUMPAGES';

    public const DATE = 'DATE';

    public const TIME = 'TIME';

    public const MERGEFIELD = 'MERGEFIELD';

    public function __construct(
        public string $instruction,
        public RunStyle $style = new RunStyle,
    ) {}

    public static function page(RunStyle $style = new RunStyle): self
    {
        return new self(self::PAGE, $style);
    }

    public static function totalPages(RunStyle $style = new RunStyle): self
    {
        return new self(self::NUMPAGES, $style);
    }

    public static function date(string $format = 'dd.MM.yyyy', RunStyle $style = new RunStyle): self
    {
        return new self(self::DATE.':'.$format, $style);
    }

    public static function time(string $format = 'HH:mm', RunStyle $style = new RunStyle): self
    {
        return new self(self::TIME.':'.$format, $style);
    }

    public static function mergeField(string $name, RunStyle $style = new RunStyle): self
    {
        return new self(self::MERGEFIELD.':'.$name, $style);
    }

    /**
     * Field kind without the format spec (PAGE, DATE, etc.).
     */
    public function kind(): string
    {
        $colon = strpos($this->instruction, ':');

        return $colon === false ? $this->instruction : substr($this->instruction, 0, $colon);
    }

    /**
     * Format spec (substring after the colon), or empty string.
     */
    public function format(): string
    {
        $colon = strpos($this->instruction, ':');

        return $colon === false ? '' : substr($this->instruction, $colon + 1);
    }
}
