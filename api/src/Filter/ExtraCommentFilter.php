<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Metadata\FilterInterface;

class ExtraCommentFilter implements FilterInterface
{
    public const FILTER_NAME = 'extraComment';

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            self::FILTER_NAME => [
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
