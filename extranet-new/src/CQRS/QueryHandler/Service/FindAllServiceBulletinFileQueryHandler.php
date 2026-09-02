<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Service;

use App\CQRS\Query\Service\FindAllServiceBulletinFileQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\ServiceBulletinFile;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllServiceBulletinFileQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function __invoke(FindAllServiceBulletinFileQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(
            resource: ServiceBulletinFile::class,
            criteria: [
                'query' => [
                    'parentId' => $query->id,
                ],
            ]
        );
    }
}
