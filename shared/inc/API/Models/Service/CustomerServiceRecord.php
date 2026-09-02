<?php

declare(strict_types=1);

namespace Shared\Models\Service;

use Shared\Models\Common\Airport;
use Shared\Models\Manufacturing\EquipmentRecord;
use Shared\Ressources\User;

class CustomerServiceRecord
{
    public function __construct(
        public string $iri,
        public \DateTime $createdAt,
        public \DateTime $updatedAt,
        public \DateTime $deletedAt,
        public \DateTime $plannedAt,
        public string $description,
        public User $createdBy,
        public array $equipmentRecord,
        public array $airport,
        public array $interventions,
        public string $status,
        public int $id,
        public bool $close,
        public string $type,
        public array|null $openIntervention = null,
    ) {
    }
}