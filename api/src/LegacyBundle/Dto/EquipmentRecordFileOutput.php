<?php

declare(strict_types=1);

namespace App\LegacyBundle\Dto;

final class EquipmentRecordFileOutput
{
    public function __construct(
        public readonly int $id,
        public readonly int $parentId,
        public readonly ?string $description,
        public readonly ?string $date,
        public readonly string $filename,
        public readonly string $displayFilename,
    ) {
    }
}
