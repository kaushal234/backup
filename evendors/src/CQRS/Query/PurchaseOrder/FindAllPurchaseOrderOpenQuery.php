<?php

declare(strict_types=1);

namespace App\CQRS\Query\PurchaseOrder;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\PurchaseOrder;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @implements QueryInterface<AccessibleCollectionInterface<PurchaseOrder>>
 */
final class FindAllPurchaseOrderOpenQuery implements QueryInterface
{
}
