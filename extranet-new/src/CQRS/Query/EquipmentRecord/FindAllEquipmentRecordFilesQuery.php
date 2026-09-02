<?php

declare(strict_types=1);

namespace App\CQRS\Query\EquipmentRecord;

use App\CQRS\Query\QueryInterface;

final class FindAllEquipmentRecordFilesQuery implements QueryInterface
{
    /**
     * @param int $legacyId the legacy equipment record id (service.id) the files are attached to
     */
    public function __construct(
        public int $legacyId,
    ) {
    }
}
