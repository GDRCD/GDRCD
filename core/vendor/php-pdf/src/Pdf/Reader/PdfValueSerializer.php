<?php

declare(strict_types=1);

namespace Dskripchenko\PhpPdf\Pdf\Reader;

/**
 * Serializes a parsed PDF value tree back to PDF syntax, remapping indirect
 * references through a caller-supplied map.
 *
 * Shared by the merge serializer and by core-document page import: both take a
 * set of {@see PdfReference}-bearing value trees and need to re-emit them with
 * object numbers reassigned. Stream `/Length` is always restated from the
 * actual body length (correcting decrypted, AES-shortened sources).
 */
final class PdfValueSerializer
{
    /**
     * @param callable(int):int $refMap source object number → output object number
     */
    public function __construct(private $refMap)
    {
    }

    public function encode(mixed $value): string
    {
        if ($value instanceof PdfStream) {
            $items = $value->dict->all();
            $items['Length'] = strlen($value->raw);
            return $this->encodeDictionary($items) . "\nstream\n" . $value->raw . "\nendstream";
        }
        if ($value instanceof PdfDictionary) {
            return $this->encodeDictionary($value->all());
        }
        if ($value instanceof PdfReference) {
            return ($this->refMap)($value->number) . ' 0 R';
        }
        if ($value instanceof PdfName) {
            return '/' . $this->encodeName($value->value);
        }
        if ($value instanceof PdfString) {
            return '<' . bin2hex($value->bytes) . '>';
        }
        if ($value instanceof PdfNull) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value)) {
            return (string) $value;
        }
        if (is_float($value)) {
            return $this->encodeFloat($value);
        }
        if (is_array($value)) {
            return '[' . implode(' ', array_map(fn ($v) => $this->encode($v), $value)) . ']';
        }
        return 'null';
    }

    /**
     * @param array<string,mixed> $items
     */
    private function encodeDictionary(array $items): string
    {
        $out = '<<';
        foreach ($items as $key => $value) {
            $out .= '/' . $this->encodeName((string) $key) . ' ' . $this->encode($value);
        }
        return $out . '>>';
    }

    private function encodeName(string $name): string
    {
        return preg_replace_callback(
            '/[^\x21-\x7E]|[#\/()<>\[\]{}%]/',
            static fn (array $m): string => '#' . bin2hex($m[0]),
            $name,
        ) ?? $name;
    }

    private function encodeFloat(float $value): string
    {
        if ($value === floor($value) && abs($value) < 1e15) {
            return (string) (int) $value;
        }
        return rtrim(rtrim(sprintf('%.6f', $value), '0'), '.');
    }
}
