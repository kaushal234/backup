<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\ManualDocument;

use App\CQRS\Query\ManualDocument\FindManualDocumentQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\ManualDocument;
use App\Sdk\Resource\ResourceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
readonly class FindManualDocumentQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private Client $client
    ) {
    }

    public function __invoke(FindManualDocumentQuery $query): ResourceInterface
    {
        return $this->client->find(ManualDocument::class, ['resource_id' => $query->id]);
    }
}
