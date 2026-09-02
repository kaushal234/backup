<?php

declare(strict_types=1);

namespace App\CQRS\Query\TechnicianOnCall;

use App\CQRS\Query\QueryInterface;

final class FindTechnicianOnCallWithTokenQuery implements QueryInterface
{
    public function __construct(
        public readonly int $id,
        public readonly string $token,
    ) {
    }
}
