<?php

declare(strict_types=1);

namespace App\CQRS\Query\ManualDocument;

use App\CQRS\Query\QueryInterface;

final readonly class FindManualDocumentQuery implements QueryInterface
{
    public function __construct(
        public int $id,
    ) {
    }
}
