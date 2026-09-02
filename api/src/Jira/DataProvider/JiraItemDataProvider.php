<?php

declare(strict_types=1);

namespace App\Jira\DataProvider;

use ApiPlatform\Metadata\Operation;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class JiraItemDataProvider extends AbstractJiraDataProvider
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($context[AbstractNormalizer::GROUPS])) {
            $context = array_merge($context, $operation->getNormalizationContext() ?? []);
        }

        $client = $this->getClient($operation);

        try {
            $response = $client->doRequest($this->getOperation($operation), $uriVariables['id']);
            if ($response->getStatusCode() > 400) {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }

        return $this->serializer->deserialize($response->getContent(), $operation->getClass(), 'jsonld', $context + ['operation' => $operation]);
    }
}
