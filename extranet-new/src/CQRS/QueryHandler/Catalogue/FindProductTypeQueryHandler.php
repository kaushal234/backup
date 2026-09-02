<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Catalogue;

use App\CQRS\Query\Catalogue\FindProductTypeQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\ProductType;
use App\Sdk\Resource\ResourceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindProductTypeQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindProductTypeQuery $query): ResourceInterface
    {
        return $this->client->find(ProductType::class, ['resource_id' => $query->id]);
    }
}
