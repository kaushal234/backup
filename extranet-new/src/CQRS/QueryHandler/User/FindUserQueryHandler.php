<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\User;

use App\CQRS\Query\User\FindUserQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\User;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindUserQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindUserQuery $query): ResourceInterface
    {
        return $this->client->find(User::class, ['resource_id' => $query->id]);
    }
}
