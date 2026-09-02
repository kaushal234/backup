<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\PurchaseOrder;

use App\CQRS\Query\PurchaseOrder\FindOnePurchaseOrderQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\PurchaseOrder;

final class FindOnePurchaseOrderQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function __invoke(FindOnePurchaseOrderQuery $query): PurchaseOrder
    {
        $identifier = (string) $query->id;
        $order = $this->client->find(PurchaseOrder::class, $identifier);

        return $order;
    }
}
