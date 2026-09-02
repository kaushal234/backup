<?php

declare(strict_types=1);

namespace App\Jira\DataProvider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Jira\Http\JiraClientInterface;
use App\Jira\Resolver\JiraClientResolver;
use App\Jira\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\Jira\SourceProvider\SourceProvider;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractJiraDataProvider implements ProviderInterface
{
    public function __construct(
        protected readonly JiraClientResolver $clientResolver,
        protected readonly SourceProvider $sourceProvider,
        protected readonly SerializerInterface $serializer,
    ) {
    }

    public function getClient(Operation $operation): JiraClientInterface
    {
        return $this->clientResolver->resolve($operation);
    }

    public function getOperation(Operation $operation): string
    {
        $resourceSourceProvider = $this->getResourceSourceProvider($operation);

        return $operation instanceof Get ? $resourceSourceProvider->getItemReadOperation() : $resourceSourceProvider->getCollectionReadOperation();
    }

    public function getResourceSourceProvider(Operation $operation): ResourceSourceProviderInterface
    {
        return $this->sourceProvider->getResourceSourceProvider($operation->getClass());
    }

    protected function getParameters(Operation $operation, array $context = []): array
    {
        $resourceSourceProvider = $this->getResourceSourceProvider($operation);

        $parameters = [];
        if (empty($options = $resourceSourceProvider->getUrlOptions($context))) {
            return $parameters;
        }

        return ['query' => $options];
    }
}
