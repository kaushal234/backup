<?php

declare(strict_types=1);

namespace App\CQRS\Query\PurchaseOrder;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\PurchaseOrder;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @implements QueryInterface<AccessibleCollectionInterface<PurchaseOrder>>
 */
final class FindReportPurchaseOrderQuery implements QueryInterface
{
    /**
     * @param array<string, array<string,string>|bool> $resources
     */
    public function __construct(
        public readonly array $resources,
    ) {
    }
}
