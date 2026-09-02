<?php

declare(strict_types=1);

namespace App\Jira\Resolver;

use ApiPlatform\Metadata\Operation;

final class JiraContextResolver
{
    public function resolve(Operation $operation): string
    {
        return $operation->getExtraProperties()['jira_context'] ?? 'jira';
    }
}
