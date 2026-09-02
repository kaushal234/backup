<?php

declare(strict_types=1);

namespace App\Filter\MIS\Gitlab;

use ApiPlatform\Metadata\FilterInterface;

final class MergeRequestFilter implements FilterInterface
{
    public function getDescription(string $resourceClass): array
    {
        return [
            'merged_after' => [
                'property' => 'merged_after',
                'type' => 'string',
                'required' => false,
                'description' => 'ISO8601. Only MRs with merged_at >= this (exact filtering done in provider).',
                'schema' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'example' => '2025-09-01T00:00:00Z',
                ],
            ],
            'merged_before' => [
                'property' => 'merged_before',
                'type' => 'string',
                'required' => false,
                'description' => 'ISO8601. Only MRs with merged_at < this (exact filtering done in provider).',
                'schema' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'example' => '2025-10-01T00:00:00Z',
                ],
            ],
            'author' => [
                'property' => 'author',
                'type' => 'string',
                'required' => false,
                'description' => 'Author username',
            ],
            'order[updated_at]' => [
                'property' => 'updated_at',
                'type' => 'string',
                'required' => false,
                'description' => 'Order by updated date. Allowed values: asc, desc.',
                'schema' => [
                    'type' => 'string',
                    'enum' => ['asc', 'desc'],
                    'example' => 'desc',
                ],
            ],
            'order[created_at]' => [
                'property' => 'created_at',
                'type' => 'string',
                'required' => false,
                'description' => 'Order by created date. Allowed values: asc, desc.',
                'schema' => [
                    'type' => 'string',
                    'enum' => ['asc', 'desc'],
                    'example' => 'desc',
                ],
            ],
            'order[merged_at]' => [
                'property' => 'merged_at',
                'type' => 'string',
                'required' => false,
                'description' => 'Order by merged date. Allowed values: asc, desc.',
                'schema' => [
                    'type' => 'string',
                    'enum' => ['asc', 'desc'],
                    'example' => 'desc',
                ],
            ],
        ];
    }
}
