<?php

declare(strict_types=1);

namespace App\CQRS\Query\User;

use App\CQRS\Query\QueryInterface;

final class FindUserQuery implements QueryInterface
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}
