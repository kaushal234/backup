<?php

declare(strict_types=1);

namespace App\AI\Dto\Service;

use App\AI\Dto\Common\AirportModel;
use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Sales\CustomerModel;
use App\AI\Dto\Service\TechnicianOnCall\SurveyModel;
use App\AI\Dto\Support\EquipmentRecordModel;

final readonly class TechnicianOnCallModel
{
    /**
     * @param string[] $tags
     */
    public function __construct(
        public int $id,
        public ?int $legacyId,
        public string $status,
        public string $title,
        public string $description,
        public \DateTimeInterface $createdAt,
        public ?\DateTimeInterface $solvedAt,
        public ?\DateTimeInterface $updatedAt,
        public ?PeopleModel $createdBy,
        public ?PeopleModel $technician,
        public ?PeopleModel $assignee,
        public ?PeopleModel $mainContact,
        public ?CustomerModel $customer,
        public ?EquipmentRecordModel $equipmentRecord,
        public ?string $serialNumber,
        public ?LocationModel $serviceOrganizationLocation,
        public ?AirportModel $airport,
        public string $indiceFactor,
        public ?string $unitOperationalStatus,
        public ?string $type,
        public string $activityType,
        public ?string $errorCodes,
        public ?string $symptoms,
        public ?string $rootCause,
        public ?string $solution,
        public ?string $thirdPartyName,
        public ?string $thirdPartyRef,
        public ?int $thirdPartyHours,
        public ?string $thirdPartyJobDescription,
        public bool $factoryFlag,
        public bool $confidential,
        public ?int $warrantyLegacyId,
        public ?int $hourMeter,
        public array $tags,
        public ?SurveyModel $survey,
    ) {
    }
}
