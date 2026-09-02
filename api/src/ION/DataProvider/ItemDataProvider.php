<?php

declare(strict_types=1);

namespace App\ION\DataProvider;

use ApiPlatform\Metadata\Operation;
use App\ExternalERP\Resolver\OperationResolverInterface;
use App\Http\LnClient;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

readonly class ItemDataProvider extends AbstractDataProvider
{
    public function __construct(
        LnClient $client,
        OperationResolverInterface $operationResolver,
        private SerializerInterface $serializer)
    {
        parent::__construct($client, $operationResolver);
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($context[AbstractNormalizer::GROUPS])) {
            $context = array_merge($context, $operation->getNormalizationContext() ?? []);
        }

        try {
            $response = $this->client->doRequest($this->getOperation($operation, $uriVariables));
            if ($response->getStatusCode() >= 400) {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }

        return $this->serializer->deserialize($response->getContent(), $operation->getClass(), 'jsonld', $context + ['operation' => $operation]);
    }
}
