<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler;

use App\CQRS\Query\FindPaginateCountriesQuery;
use App\Sdk\Client;
use App\Sdk\PageInterface;
use App\Sdk\Resource\Country;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindPaginateCountriesQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindPaginateCountriesQuery $query): PageInterface
    {
        return $this->client->paginate(
            resource: Country::class,
            page: $query->page,
            itemsPerPage: $query->itemsPerPage,
            criteria: [
                'normalization_groups_override' => ['country_list'],
                ...$query->options,
            ]
        );
    }
}
