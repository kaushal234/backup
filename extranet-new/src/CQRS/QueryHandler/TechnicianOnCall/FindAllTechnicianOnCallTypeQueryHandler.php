<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\TechnicianOnCall;

use App\CQRS\Query\TechnicianOnCall\FindAllTechnicianOnCallTypeQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\TechnicianOnCallType;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllTechnicianOnCallTypeQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function __invoke(FindAllTechnicianOnCallTypeQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(TechnicianOnCallType::class);
    }
}
