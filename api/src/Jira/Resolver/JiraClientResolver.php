<?php

declare(strict_types=1);

namespace App\Jira\Resolver;

use ApiPlatform\Metadata\Operation;
use App\Jira\Http\JiraClientInterface;
use App\Jira\Registry\JiraClientRegistry;

class JiraClientResolver
{
    public function __construct(
        private JiraClientRegistry $clients,
        private JiraContextResolver $contextResolver,
    ) {
    }

    public function resolve(Operation $operation): JiraClientInterface
    {
        return $this->clients->get($this->contextResolver->resolve($operation));
    }
}
