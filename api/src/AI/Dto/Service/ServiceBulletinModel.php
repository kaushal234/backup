<?php

declare(strict_types=1);

namespace App\AI\Dto\Service;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;

final readonly class ServiceBulletinModel
{
    public function __construct(
        public int $parentId,
        public string $status,
        public string $category,
        public ?string $categoryReason,
        public string $type,
        public int $importanceFactor,
        public string $confidential,
        public string $title,
        public string $description,
        public int $laborHours,
        public int $numberOfTechniciansNeeded,
        public bool $partsNeeded,
        public string $factoryPartAvailabilityStatus,
        public \DateTimeInterface $createdAt,
        public ?\DateTimeInterface $ssdApprovedAt,
        public ?\DateTimeInterface $ssdDecidedAt,
        public ?\DateTimeInterface $implementedAt,
        public ?\DateTimeInterface $closedAt,
        public ?PeopleModel $poster,
        public ?LocationModel $factory,
    ) {
    }
}
