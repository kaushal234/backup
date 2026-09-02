<?php

declare(strict_types=1);

namespace App\AI\Dto\Quality;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Parts\SupplierCorrectiveActionRequestPartModel;

final readonly class SupplierCorrectiveActionRequestModel
{
    /**
     * @param list<SupplierCorrectiveActionRequestPartModel> $parts
     */
    public function __construct(
        public string $status,
        public \DateTimeInterface $createdAt,
        public ?\DateTimeInterface $closedAt,
        public ?\DateTimeInterface $approvedAt,
        public string $iFactor,
        public string $shortDescription,
        public string $description,
        public ?string $issueOrigin,
        public ?string $correctiveAction,
        public ?string $commercialAgreement,
        public ?string $verificationDescription,
        public ?string $preventiveAction,
        public ?string $conclusion,
        public string $supplierName,
        public string $supplierNumber,
        public ?int $supplierErp,
        public LocationModel $factory,
        public ?PeopleModel $representative,
        public ?PeopleModel $leader,
        public array $parts,
    ) {
    }
}
