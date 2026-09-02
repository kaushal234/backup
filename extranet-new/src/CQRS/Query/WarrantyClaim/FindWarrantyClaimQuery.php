<?php

declare(strict_types=1);

namespace App\CQRS\Query\WarrantyClaim;

use App\CQRS\Query\QueryInterface;

final class FindWarrantyClaimQuery implements QueryInterface
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}
