<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Service;

use App\CQRS\Query\Service\FindServiceBulletinQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\ServiceBulletin;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindServiceBulletinRecordQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindServiceBulletinQuery $query): ResourceInterface
    {
        return $this->client->find(ServiceBulletin::class, [
            'resource_id' => $query->id,
            'customerLegacyId' => $query->customerLegacyId,
        ]);
    }
}
