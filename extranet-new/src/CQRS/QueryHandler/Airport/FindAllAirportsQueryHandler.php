<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Airport;

use App\CQRS\Query\Airport\FindAllAirportsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\Airport;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
readonly class FindAllAirportsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private Client $client
    ) {
    }

    public function __invoke(FindAllAirportsQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(Airport::class, [
            'query' => [
                'normalization_groups_override' => ['airport_list'],
                ...$query->options,
            ],
        ]);
    }
}
