<?php

declare(strict_types=1);

namespace App\CQRS\Query\Service;

use App\CQRS\Query\QueryInterface;

final class FindServiceBulletinQuery implements QueryInterface
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $customerLegacyId = null,
    ) {
    }
}
