<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Airport;

use App\CQRS\Query\Airport\FindPaginateAirportsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\PageInterface;
use App\Sdk\Resource\Airport;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
readonly class FindPaginateAirportsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private Client $client
    ) {
    }

    public function __invoke(FindPaginateAirportsQuery $query): PageInterface
    {
        return $this->client->paginate(
            resource: Airport::class,
            page: $query->page,
            itemsPerPage: $query->itemsPerPage,
            criteria: [
                ...$query->options,
            ]
        );
    }
}
