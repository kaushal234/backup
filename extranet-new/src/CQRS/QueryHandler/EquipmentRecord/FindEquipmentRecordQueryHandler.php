<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\EquipmentRecord;

use App\CQRS\Query\EquipmentRecord\FindEquipmentRecordQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\EquipmentRecord;
use App\Sdk\Resource\ResourceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindEquipmentRecordQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindEquipmentRecordQuery $query): ResourceInterface
    {
        return $this->client->find(EquipmentRecord::class, ['resource_id' => $query->id]);
    }
}
