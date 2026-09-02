<?php

declare(strict_types=1);

namespace App\CQRS\Query\Activity;

use App\CQRS\Query\QueryInterface;

final class FindAllCommentQuery implements QueryInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public ?int $page = null,
        public ?int $itemsPerPage = null,
        public array $options = [],
    ) {
    }
}
