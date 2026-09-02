<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\SupplierCorrectiveActionRequest;

use App\CQRS\Query\SupplierCorrectiveActionRequest\FindAllSupplierCorrectiveActionRequestsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;
use Psl\Collection\AccessibleCollectionInterface;

final class FindAllSupplierCorrectiveActionRequestsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return AccessibleCollectionInterface<int, SupplierCorrectiveActionRequest>
     */
    public function __invoke(FindAllSupplierCorrectiveActionRequestsQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(SupplierCorrectiveActionRequest::class);
    }
}
