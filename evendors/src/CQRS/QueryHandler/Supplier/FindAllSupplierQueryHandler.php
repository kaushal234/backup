<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Supplier;

use App\CQRS\Query\Supplier\FindAllSuppliersQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\Supplier;
use Psl\Collection\AccessibleCollectionInterface;

final class FindAllSupplierQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return AccessibleCollectionInterface<int, Supplier>
     */
    public function __invoke(FindAllSuppliersQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(Supplier::class);
    }
}
