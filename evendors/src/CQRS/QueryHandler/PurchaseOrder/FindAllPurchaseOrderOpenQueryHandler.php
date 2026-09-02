<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\PurchaseOrder;

use App\CQRS\Query\PurchaseOrder\FindAllPurchaseOrderOpenQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\PurchaseOrder;
use Psl\Collection\AccessibleCollectionInterface;

final class FindAllPurchaseOrderOpenQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return AccessibleCollectionInterface<int, PurchaseOrder>
     */
    public function __invoke(FindAllPurchaseOrderOpenQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(PurchaseOrder::class, ['onlyOpenedLines' => 1]);
    }
}
