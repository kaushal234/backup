<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\EquipmentRecord;

use App\CQRS\Query\EquipmentRecord\FindAllEquipmentRecordsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Page;
use App\Sdk\Resource\EquipmentRecord;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllEquipmentRecordsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function __invoke(FindAllEquipmentRecordsQuery $query): Page
    {
        return $this->client->paginate(
            resource: EquipmentRecord::class,
            page: $query->page,
            itemsPerPage: $query->itemsPerPage,
            criteria: [
                ...$query->options,
            ]
        );
    }
}
