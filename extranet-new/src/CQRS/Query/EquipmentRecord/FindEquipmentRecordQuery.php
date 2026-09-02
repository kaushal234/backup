<?php

declare(strict_types=1);

namespace App\CQRS\Query\EquipmentRecord;

use App\CQRS\Query\QueryInterface;

final class FindEquipmentRecordQuery implements QueryInterface
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}
