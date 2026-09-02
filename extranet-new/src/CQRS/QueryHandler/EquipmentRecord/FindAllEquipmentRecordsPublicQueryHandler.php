<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\EquipmentRecord;

use App\CQRS\Query\EquipmentRecord\FindAllEquipmentRecordsPublicQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Page;
use App\Sdk\Resource\EquipmentRecordPublic;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllEquipmentRecordsPublicQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function __invoke(FindAllEquipmentRecordsPublicQuery $query): Page
    {
        return $this->client->paginate(
            resource: EquipmentRecordPublic::class,
            page: $query->page,
            itemsPerPage: $query->itemsPerPage,
            criteria: [
                ...$query->options,
            ]
        );
    }
}
