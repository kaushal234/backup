<?php

declare(strict_types=1);

namespace App\CQRS\Query\TechnicianOnCall;

use App\CQRS\Query\QueryInterface;

final class FindTechnicianOnCallQuery implements QueryInterface
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}
