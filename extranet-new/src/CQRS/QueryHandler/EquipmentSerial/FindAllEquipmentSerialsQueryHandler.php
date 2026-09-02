<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\EquipmentSerial;

use App\CQRS\Query\EquipmentSerial\FindAllEquipmentSerialsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\EquipmentSerial;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllEquipmentSerialsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function __invoke(FindAllEquipmentSerialsQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(
            resource: EquipmentSerial::class,
            criteria: [
                'equipmentRecord.serialNumber' => $query->equipmentRecordSerialNumber,
                'schematics' => $query->schematics,
                'normalization_groups' => ['signal_code'],
            ],
        );
    }
}
