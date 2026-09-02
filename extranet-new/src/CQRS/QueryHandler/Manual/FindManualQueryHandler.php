<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Manual;

use App\CQRS\Query\Manual\FindManualQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\Manual;
use App\Sdk\Resource\ResourceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
readonly class FindManualQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private Client $client
    ) {
    }

    public function __invoke(FindManualQuery $query): ResourceInterface
    {
        return $this->client->find(Manual::class, ['resource_id' => $query->id]);
    }
}
