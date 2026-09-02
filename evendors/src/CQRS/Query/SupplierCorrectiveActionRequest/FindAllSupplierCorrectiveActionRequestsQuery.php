<?php

declare(strict_types=1);

namespace App\CQRS\Query\SupplierCorrectiveActionRequest;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @implements QueryInterface<AccessibleCollectionInterface<int, SupplierCorrectiveActionRequest>>
 */
final class FindAllSupplierCorrectiveActionRequestsQuery implements QueryInterface
{
}
