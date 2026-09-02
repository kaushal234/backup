<?php

declare(strict_types=1);

namespace App\Link\Formatter;

interface FormatterLinkInterface
{
    public static function formatValue(string $value): ?string;
}
