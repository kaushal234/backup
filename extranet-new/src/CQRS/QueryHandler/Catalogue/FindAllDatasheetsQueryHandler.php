<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Catalogue;

use App\CQRS\Query\Catalogue\FindAllDatasheetsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\Datasheet;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllDatasheetsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindAllDatasheetsQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(Datasheet::class, [
            'query' => [
                'family' => $query->productFamilyIri,
            ],
        ]);
    }
}
