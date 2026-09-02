<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Catalogue;

use App\CQRS\Query\Catalogue\FindPaginateProductsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\PageInterface;
use App\Sdk\Resource\Product;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindPaginateProductQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindPaginateProductsQuery $query): PageInterface
    {
        return $this->client->paginate(
            resource: Product::class,
            page: $query->page,
            itemsPerPage: $query->itemsPerPage,
            criteria: [
                ...$query->options,
            ]
        );
    }
}
