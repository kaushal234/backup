<?php

declare(strict_types=1);

namespace App\Filter;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use ApiPlatform\Metadata\FilterInterface;

#[FeatureDoc(path: 'export.md')]
class ColumnsFilter implements FilterInterface
{
    final public const PARAMETER_NAME = 'columns';

    public function getDescription(string $resourceClass): array
    {
        return [
            self::PARAMETER_NAME => [
                'property' => self::PARAMETER_NAME,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
