<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\SupplierCorrectiveActionRequest;

use App\CQRS\Query\SupplierCorrectiveActionRequest\FindSupplierCorrectiveActionRequestUsingIdQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;

final class FindSupplierCorrectiveActionRequestUsingIdQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function __invoke(FindSupplierCorrectiveActionRequestUsingIdQuery $query): SupplierCorrectiveActionRequest
    {
        return $this->client->find(SupplierCorrectiveActionRequest::class, (string) $query->id);
    }
}
