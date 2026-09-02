<?php

declare(strict_types=1);

namespace App\AI\Factory\Quality;

use App\AI\Dto\Quality\WarrantyClaim\WarrantyClaimModel;
use App\AI\Dto\Quality\WarrantyClaim\WarrantyClaimPartModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use LegacyBundle\Entity\Quality\WarrantyClaim;
use LegacyBundle\Entity\Quality\WarrantyClaimPart;

final readonly class WarrantyClaimModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return WarrantyClaim::class === $class;
    }

    /**
     * @param WarrantyClaim $entity
     */
    public function create(object $entity): WarrantyClaimModel
    {
        return new WarrantyClaimModel(
            status: $entity->status,
            claimDate: $entity->claimDate,
            type: $entity->type,
            equipmentModel: $entity->equipmentModel,
            serialNumber: $entity->serialNumber,
            equipmentHours: $entity->equipmentHours,
            customerName: $entity->customerName,
            claimantDetails: $entity->claimantDetails,
            equipmentLocation: $entity->equipmentLocation,
            manufacturingLocation: null === $entity->manufacturingLocation ? null : $this->locationModelFactory->create($entity->manufacturingLocation),
            salesOrganization: null === $entity->salesOrganization ? null : $this->locationModelFactory->create($entity->salesOrganization),
            enteredBy: null === $entity->enteredBy ? null : $this->peopleModelFactory->create($entity->enteredBy),
            details: $entity->details,
            description: $entity->description,
            extranetProblemDescription: $entity->extranetProblemDescription,
            failureCode1: $entity->failureCode1,
            failureCode2: $entity->failureCode2,
            intervention: $entity->intervention,
            estimatedManHours: $entity->estimatedManHours,
            productionManagerAcceptUser: null === $entity->productionManagerAcceptUser ? null : $this->peopleModelFactory->create($entity->productionManagerAcceptUser),
            productionManagerAcceptDate: $entity->productionManagerAcceptDate,
            productionManagerComments: $entity->productionManagerComments,
            partsDeliveryDate: $entity->partsDeliveryDate,
            partsCourier: $entity->partsCourier,
            returnParts: $entity->returnParts,
            serviceAcceptDate: $entity->serviceAcceptDate,
            serviceDeliveryDate: $entity->serviceDeliveryDate,
            serviceComments: $entity->serviceComments,
            serviceShippingInstructions: $entity->serviceShippingInstructions,
            technician: $entity->technician,
            technicianTravelExpenseCost: $entity->technicianTravelExpenseCost,
            technicianLabourCost: $entity->technicianLabourCost,
            partsCost: $entity->partsCost,
            costNotes: $entity->costNotes,
            criticalPartFailing: $entity->criticalPartFailing,
            partReturnAddress: $entity->partReturnAddress,
            partReturnDate: $entity->partReturnDate,
            partsOrderReference: $entity->partsOrderReference,
            parts: array_values(array_map(
                static fn (WarrantyClaimPart $part) => new WarrantyClaimPartModel(
                    supplyIt: $part->supplyIt,
                    partNumber: $part->partNumber,
                    partDescription: $part->partDescription,
                    brand: $part->brand,
                    quantity: $part->quantity,
                    quantityReturned: $part->quantityReturned,
                    returnedDate: $part->returnedDate,
                    unitOfMeasure: $part->unitOfMeasure,
                    failureType: $part->failureType,
                    failureSystem: $part->failureSystem,
                    replacementSerialNumber: $part->replacementSerialNumber,
                    notes: $part->notes,
                ),
                $entity->parts->toArray(),
            )),
        );
    }
}
