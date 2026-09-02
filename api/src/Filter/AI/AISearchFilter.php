<?php

declare(strict_types=1);

namespace App\Filter\AI;

use ApiPlatform\Metadata\FilterInterface;

class AISearchFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            'query' => ['property' => 'query', 'type' => 'string'],
        ];
    }
}
