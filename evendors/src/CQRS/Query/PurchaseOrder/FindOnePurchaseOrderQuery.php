<?php

declare(strict_types=1);

namespace App\CQRS\Query\PurchaseOrder;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\PurchaseOrder;

/**
 * @implements QueryInterface<PurchaseOrder>
 */
final class FindOnePurchaseOrderQuery implements QueryInterface
{
    public function __construct(
        public readonly int|string $id,
        public readonly ?int $erp = null,
    ) {
    }
}
