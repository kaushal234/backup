<?php

declare(strict_types=1);

namespace App\AI\Dto\Quality\WarrantyClaim;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;

final readonly class WarrantyClaimModel
{
    /**
     * @param list<WarrantyClaimPartModel> $parts
     */
    public function __construct(
        public string $status,
        public ?\DateTimeInterface $claimDate,
        public string $type,
        public string $equipmentModel,
        public ?string $serialNumber,
        public ?int $equipmentHours,
        public ?string $customerName,
        public ?string $claimantDetails,
        public ?string $equipmentLocation,
        public ?LocationModel $manufacturingLocation,
        public ?LocationModel $salesOrganization,
        public ?PeopleModel $enteredBy,
        public ?string $details,
        public ?string $description,
        public string $extranetProblemDescription,
        public string $failureCode1,
        public string $failureCode2,
        public string $intervention,
        public ?int $estimatedManHours,
        public ?PeopleModel $productionManagerAcceptUser,
        public ?\DateTimeInterface $productionManagerAcceptDate,
        public string $productionManagerComments,
        public ?\DateTimeInterface $partsDeliveryDate,
        public ?string $partsCourier,
        public string $returnParts,
        public ?\DateTimeInterface $serviceAcceptDate,
        public ?\DateTimeInterface $serviceDeliveryDate,
        public string $serviceComments,
        public string $serviceShippingInstructions,
        public ?string $technician,
        public ?string $technicianTravelExpenseCost,
        public ?string $technicianLabourCost,
        public ?string $partsCost,
        public string $costNotes,
        public string $criticalPartFailing,
        public string $partReturnAddress,
        public ?\DateTimeInterface $partReturnDate,
        public string $partsOrderReference,
        public array $parts,
    ) {
    }
}
