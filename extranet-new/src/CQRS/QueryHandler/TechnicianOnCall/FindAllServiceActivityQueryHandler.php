<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\TechnicianOnCall;

use App\CQRS\Query\TechnicianOnCall\FindAllServiceActivityQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\ServiceActivity;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllServiceActivityQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function __invoke(FindAllServiceActivityQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(ServiceActivity::class);
    }
}
