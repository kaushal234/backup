<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Location;

use App\CQRS\Query\Location\FindAllLocationsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\Location;
use Psl\Collection\AccessibleCollectionInterface;

final class FindAllLocationsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return AccessibleCollectionInterface<int, Location>
     */
    public function __invoke(FindAllLocationsQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(Location::class, array_merge($query->options, ['order' => ['name' => 'ASC']]));
    }
}
