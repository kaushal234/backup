<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Catalogue;

use App\CQRS\Query\Catalogue\FindAllProductsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\Product;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllProductQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindAllProductsQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(Product::class, [
            'query' => [
                ...$query->options,
            ],
        ]);
    }
}
