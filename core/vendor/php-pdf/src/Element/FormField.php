<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Element;

/**
 * AcroForm interactive form field.
 *
 * Supported types:
 *  - text (single-line)
 *  - text-multiline
 *  - password (masked input)
 *  - checkbox
 *  - radio-group (single AST node produces multiple widgets sharing the same /T)
 *  - combo (dropdown)
 *  - list (listbox)
 *  - signature (signature placeholder for PKCS#7)
 *  - submit / reset / push buttons
 *
 * JavaScript additional actions (validate, calculate, format, keystroke)
 * are emitted as /AA entries; readers without JavaScript support skip them.
 *
 * Appearance streams are not generated — readers handle widget rendering
 * by themselves using the field type and dictionary.
 */
final readonly class FormField implements BlockElement
{
    public const TYPE_TEXT = 'text';

    public const TYPE_TEXT_MULTILINE = 'text-multiline';

    public const TYPE_PASSWORD = 'password';

    public const TYPE_CHECKBOX = 'checkbox';

    public const TYPE_RADIO_GROUP = 'radio-group';

    public const TYPE_COMBO = 'combo';

    public const TYPE_LIST = 'list';

    public const TYPE_SIGNATURE = 'signature';

    public const TYPE_SUBMIT_BUTTON = 'submit';

    public const TYPE_RESET_BUTTON = 'reset';

    public const TYPE_PUSH_BUTTON = 'push';

    public const SUPPORTED_TYPES = [
        self::TYPE_TEXT, self::TYPE_TEXT_MULTILINE, self::TYPE_PASSWORD,
        self::TYPE_CHECKBOX, self::TYPE_RADIO_GROUP, self::TYPE_COMBO,
        self::TYPE_LIST, self::TYPE_SIGNATURE,
        self::TYPE_SUBMIT_BUTTON, self::TYPE_RESET_BUTTON, self::TYPE_PUSH_BUTTON,
    ];

    /**
     * @param  list<string>  $options  Choices for combo / list / radio-group.
     *                                  Each entry is both export value and
     *                                  display label (for radio: also the
     *                                  visual button label).
     * @param  ?string  $validateScript  JavaScript for the /V (Validate) event;
     *                                    return false to reject input.
     * @param  ?string  $calculateScript JavaScript for /C (Calculate) — derive
     *                                    value from other fields.
     * @param  ?string  $formatScript    JavaScript for /F (Format) — modify
     *                                    display value.
     * @param  ?string  $keystrokeScript JavaScript for /K (Keystroke) —
     *                                    per-keypress filter.
     * @param  ?string  $buttonCaption   Button label (push/submit/reset);
     *                                    defaults to the type label.
     * @param  ?string  $submitUrl       Submit endpoint URL for TYPE_SUBMIT_BUTTON.
     * @param  ?string  $clickScript     /A click action JavaScript.
     */
    public function __construct(
        public string $name,
        public string $type = self::TYPE_TEXT,
        public string $defaultValue = '',
        public float $widthPt = 200.0,
        public float $heightPt = 20.0,
        public float $spaceBeforePt = 4.0,
        public float $spaceAfterPt = 4.0,
        public ?string $tooltip = null,
        public bool $required = false,
        public bool $readOnly = false,
        public array $options = [],
        public ?string $validateScript = null,
        public ?string $calculateScript = null,
        public ?string $formatScript = null,
        public ?string $keystrokeScript = null,
        public ?string $buttonCaption = null,
        public ?string $submitUrl = null,
        public ?string $clickScript = null,
    ) {
        if (! in_array($type, self::SUPPORTED_TYPES, true)) {
            throw new \InvalidArgumentException("Unsupported FormField type: $type");
        }
        $needsOptions = in_array(
            $type,
            [self::TYPE_RADIO_GROUP, self::TYPE_COMBO, self::TYPE_LIST],
            true,
        );
        if ($needsOptions && $options === []) {
            throw new \InvalidArgumentException("$type FormField requires non-empty options[]");
        }
    }
}
