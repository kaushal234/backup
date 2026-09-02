<?php

declare(strict_types=1);

namespace App\AI\Dto\Service\CustomerServiceRecord;

use App\AI\Dto\Common\AirportModel;
use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Support\EquipmentRecordModel;

final readonly class CustomerServiceRecordModel
{
    /**
     * @param list<InterventionModel> $interventions
     */
    public function __construct(
        public string $type,
        public string $status,
        public string $title,
        public ?string $description,
        public \DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
        public ?\DateTimeInterface $plannedAt,
        public ?\DateTimeInterface $completedAt,
        public ?\DateTimeInterface $closedAt,
        public ?PeopleModel $createdBy,
        public EquipmentRecordModel $equipmentRecord,
        public ?AirportModel $airport,
        public array $interventions,
    ) {
    }
}
