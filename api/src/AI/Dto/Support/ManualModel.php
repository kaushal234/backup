<?php

declare(strict_types=1);

namespace App\AI\Dto\Support;

use App\AI\Dto\Directory\PeopleModel;

final readonly class ManualModel
{
    public function __construct(
        public ?string $description,
        public ?string $features,
        public ?string $language,
        public ?string $status,
        public ?\DateTimeInterface $createdAt,
        public ?PeopleModel $createdBy,
        public ?EquipmentSerialModel $equipmentSerial,
        public ?EquipmentRecordModel $equipmentRecord,
    ) {
    }
}
