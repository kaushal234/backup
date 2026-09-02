<?php

declare(strict_types=1);

namespace App\Jira\DataTransformer;

class PropertyToJiraField implements JiraFieldDataTransformerInterface
{
    public function __invoke($value, array $options)
    {
        return [$options['field'] => $value];
    }
}
