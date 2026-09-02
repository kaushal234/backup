<?php

declare(strict_types=1);

namespace App\CQRS\Query\EquipmentRecord;

use App\CQRS\Query\QueryInterface;

final class FindEquipmentRecordPublicQuery implements QueryInterface
{
    public function __construct(
        public readonly string $serialNumber,
    ) {
    }
}
