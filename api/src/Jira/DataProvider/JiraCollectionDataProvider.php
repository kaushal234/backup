<?php

declare(strict_types=1);

namespace App\Jira\DataProvider;

use ApiPlatform\Metadata\Operation;

class JiraCollectionDataProvider extends AbstractJiraDataProvider
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $client = $this->getClient($operation);

        return $this->serializer->deserialize($client->doRequest($this->getOperation($operation), null, $this->getParameters($operation, $context))->getContent(), \sprintf('%s[]', $operation->getClass()), 'jsonld', $context);
    }
}
