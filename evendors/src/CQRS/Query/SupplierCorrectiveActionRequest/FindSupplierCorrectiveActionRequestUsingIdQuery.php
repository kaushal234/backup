<?php

declare(strict_types=1);

namespace App\CQRS\Query\SupplierCorrectiveActionRequest;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;

/**
 * @implements QueryInterface<SupplierCorrectiveActionRequest>
 */
final class FindSupplierCorrectiveActionRequestUsingIdQuery implements QueryInterface
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}
