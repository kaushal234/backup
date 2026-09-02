<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler;

use App\CQRS\Query\FindAllCountriesQuery;
use App\Sdk\Client;
use App\Sdk\Resource\Country;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllCountriesQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindAllCountriesQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(Country::class, [
            'query' => [
                'normalization_groups_override' => ['country_list'],
                ...$query->options,
            ],
        ]);
    }
}
