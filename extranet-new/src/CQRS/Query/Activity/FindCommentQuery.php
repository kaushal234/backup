<?php

declare(strict_types=1);

namespace App\CQRS\Query\Activity;

use App\CQRS\Query\QueryInterface;

final class FindCommentQuery implements QueryInterface
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}
