<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Metadata\FilterInterface;

class ReportOptionsFilter implements FilterInterface
{
    final public const PARAMETER_NAME = 'options';

    public function getDescription(string $resourceClass): array
    {
        return [
            'options' => [
                'property' => self::PARAMETER_NAME,
                'type' => 'array',
                'required' => false,
            ],
        ];
    }
}
