<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\TechnicianOnCall;

use App\CQRS\Query\TechnicianOnCall\FindTechnicianOnCallWithTokenQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\TechnicianOnCall;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindTechnicianOnCallWithTokenQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindTechnicianOnCallWithTokenQuery $query): ResourceInterface
    {
        return $this->client->find(TechnicianOnCall::class, [
            'resource_id' => $query->id,
            'token' => $query->token,
        ]);
    }
}
