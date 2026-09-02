<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Service;

use App\CQRS\Query\Service\FindAllServiceBulletinsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Page;
use App\Sdk\Resource\ServiceBulletin;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllServiceBulletinsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function __invoke(FindAllServiceBulletinsQuery $query): Page
    {
        return $this->client->paginate(
            resource: ServiceBulletin::class,
            page: $query->page,
            itemsPerPage: $query->itemsPerPage,
            criteria: $query->options,
        );
    }
}
