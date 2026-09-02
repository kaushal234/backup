<?php

declare(strict_types=1);

namespace App\Tests\Filter\Dummies;

use ApiPlatform\Doctrine\Common\Filter\OrderFilterInterface;
use ApiPlatform\Metadata\FilterInterface;

class DummyOrderFilter implements OrderFilterInterface, FilterInterface
{
    public string $orderParameterName = 'order';

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            'foo' => [
                'property' => 'foo',
                'type' => 'string',
                'required' => false,
                'description' => '',
                'strategy' => 'exact',
                'is_collection' => false,
            ],
        ];
    }
}
