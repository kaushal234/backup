<?php

declare(strict_types=1);

namespace App\CQRS\Query\PurchaseOrder;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\Comment;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @implements QueryInterface<AccessibleCollectionInterface<Comment>>
 */
final class FindAllPurchaseOrderCommentsQuery implements QueryInterface
{
    public function __construct(
        public readonly string $resource,
    ) {
    }
}
