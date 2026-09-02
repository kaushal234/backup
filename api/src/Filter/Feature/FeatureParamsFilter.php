<?php

declare(strict_types=1);

namespace App\Filter\Feature;

use ApiPlatform\Metadata\FilterInterface;

class FeatureParamsFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_RESOURCE_PROPERTY = 'resource';

    /**
     * @var string
     */
    final public const FILTER_ATTRIBUTE_PROPERTY = 'attributes';

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_RESOURCE_PROPERTY => [
                'property' => static::FILTER_RESOURCE_PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
            static::FILTER_ATTRIBUTE_PROPERTY => [
                'property' => static::FILTER_ATTRIBUTE_PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
