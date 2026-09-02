<?php

declare(strict_types=1);

namespace App\Formatter\Snappy;

interface FormatterInterface
{
    public function convert($object, $purpose, string $format, array $extraContext = []): string;
}
