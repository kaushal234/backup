<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Catalogue;

use App\CQRS\Query\Catalogue\FindAllProductTypesQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\ProductType;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllProductTypesQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindAllProductTypesQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(ProductType::class, [
            'query' => [
                ...$query->options,
                'order' => ['englishName' => 'ASC'],
            ],
        ]);
    }
}
