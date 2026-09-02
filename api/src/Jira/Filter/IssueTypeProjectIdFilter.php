<?php

declare(strict_types=1);

namespace App\Jira\Filter;

use ApiPlatform\Metadata\FilterInterface;

class IssueTypeProjectIdFilter implements FilterInterface
{
    final public const FILTER_PROPERTY = 'projectId';

    public function getDescription(string $resourceClass): array
    {
        return [
            self::FILTER_PROPERTY => [
                'property' => self::FILTER_PROPERTY,
                'type' => 'int',
                'required' => false,
                'description' => 'Jira project ID',
            ],
        ];
    }
}
