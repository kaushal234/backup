<?php

declare(strict_types=1);

namespace App\AI\Factory\Quality;

use App\AI\Dto\Parts\SupplierCorrectiveActionRequestPartModel;
use App\AI\Dto\Quality\SupplierCorrectiveActionRequestModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use App\Entity\Parts\SupplierCorrectiveActionRequestPart;
use App\Entity\Quality\SupplierCorrectiveActionRequest;

final readonly class SupplierCorrectiveActionRequestModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return SupplierCorrectiveActionRequest::class === $class;
    }

    /**
     * @param SupplierCorrectiveActionRequest $entity
     */
    public function create(object $entity): SupplierCorrectiveActionRequestModel
    {
        return new SupplierCorrectiveActionRequestModel(
            status: $entity->getStatus(),
            createdAt: $entity->createdAt,
            closedAt: $entity->closedAt,
            approvedAt: $entity->approvedAt,
            iFactor: $entity->iFactor,
            shortDescription: $entity->shortDescription,
            description: $entity->description,
            issueOrigin: $entity->issueOrigin,
            correctiveAction: $entity->correctiveAction,
            commercialAgreement: $entity->commercialAgreement,
            verificationDescription: $entity->verificationDescription,
            preventiveAction: $entity->preventiveAction,
            conclusion: $entity->conclusion,
            supplierName: $entity->getSupplierName(),
            supplierNumber: $entity->getSupplierNumber(),
            supplierErp: $entity->getSupplierErp(),
            factory: $this->locationModelFactory->create($entity->factory),
            representative: null === $entity->representative ? null : $this->peopleModelFactory->create($entity->representative),
            leader: null === $entity->leader ? null : $this->peopleModelFactory->create($entity->leader),
            parts: array_values(array_map(
                static fn (SupplierCorrectiveActionRequestPart $part) => new SupplierCorrectiveActionRequestPartModel(
                    createdAt: $part->createdAt,
                    partNumber: $part->partNumber,
                    description: $part->description,
                    quantity: $part->quantity,
                    unitOfMeasure: $part->unitOfMeasure,
                ),
                $entity->getParts()->toArray(),
            )),
        );
    }
}
