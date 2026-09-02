<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\WarrantyClaim;

use App\CQRS\Query\WarrantyClaim\FindWarrantyClaimQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\Resource\WarrantyClaim;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindWarrantyClaimQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindWarrantyClaimQuery $query): ResourceInterface
    {
        return $this->client->find(WarrantyClaim::class, ['resource_id' => $query->id]);
    }
}
