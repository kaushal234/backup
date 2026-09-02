<?php

declare(strict_types=1);

namespace App\Link\Formatter;

class FormatterLinkLocationName implements FormatterLinkInterface
{
    public function __invoke($value, array $options, string $field)
    {
        return [$field => $this->formatValue($value)];
    }

    public static function formatValue(?string $value): ?string
    {
        return null !== $value ? str_replace(' ', '_', $value) : null;
    }
}
