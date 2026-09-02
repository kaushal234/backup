<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\EquipmentRecord;

use App\CQRS\Query\EquipmentRecord\FindAllEquipmentRecordFilesQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\EquipmentRecordFile;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllEquipmentRecordFilesQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function __invoke(FindAllEquipmentRecordFilesQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(
            resource: EquipmentRecordFile::class,
            criteria: [
                'query' => [
                    'parentId' => $query->legacyId,
                ],
            ]
        );
    }
}
