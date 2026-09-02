<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\EquipmentRecord;

use App\CQRS\Query\EquipmentRecord\FindEquipmentRecordPublicQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\EquipmentRecordPublic;
use App\Sdk\Resource\ResourceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindEquipmentRecordPublicQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindEquipmentRecordPublicQuery $query): ResourceInterface
    {
        return $this->client->find(EquipmentRecordPublic::class, ['serialNumber' => $query->serialNumber]);
    }
}
