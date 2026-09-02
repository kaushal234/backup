<?php

declare(strict_types=1);

namespace App\CQRS\Query\Supplier;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\Supplier;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @implements QueryInterface<AccessibleCollectionInterface<int, Supplier>>
 */
final class FindAllSuppliersQuery implements QueryInterface
{
}
