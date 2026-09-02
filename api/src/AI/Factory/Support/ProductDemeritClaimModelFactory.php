<?php

declare(strict_types=1);

namespace App\AI\Factory\Support;

use App\AI\Dto\Support\ProductDemeritClaimModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use LegacyBundle\Entity\Support\ProductDemeritClaim;

final readonly class ProductDemeritClaimModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return ProductDemeritClaim::class === $class;
    }

    /**
     * @param ProductDemeritClaim $entity
     */
    public function create(object $entity): ProductDemeritClaimModel
    {
        return new ProductDemeritClaimModel(
            factory: null === $entity->factory ? null : $this->locationModelFactory->create($entity->factory),
            productType: $entity->productType,
            productModel: $entity->productModel,
            lastStatus: $entity->lastStatus,
            status: $entity->status,
            openingDate: $entity->openingDate,
            closingDate: $entity->closingDate,
            suspensionDate: $entity->suspensionDate,
            daysSuspended: $entity->daysSuspended,
            shortDescription: $entity->shortDescription,
            description: $entity->description,
            containmentAction: $entity->containmentAction,
            rootCause: $entity->rootCause,
            correctiveAction: $entity->correctiveAction,
            preventiveAction: $entity->preventiveAction,
            resolution: $entity->resolution,
            rejectionReason: $entity->rejectionReason,
            importanceFactor: $entity->importanceFactor,
            finalFocusWeight: $entity->finalFocusWeight,
            poster: null === $entity->poster ? null : $this->peopleModelFactory->create($entity->poster),
            initiator: null === $entity->initiator ? null : $this->peopleModelFactory->create($entity->initiator),
            assignee: null === $entity->assignee ? null : $this->peopleModelFactory->create($entity->assignee),
            verificationDescription: $entity->verificationDescription,
            statusUpdatedAt: $entity->statusUpdatedAt,
            involvesIbs: (bool) $entity->involvesIbs,
            involvesIhs: (bool) $entity->involvesIhs,
            involvesLink: (bool) $entity->involvesLink,
            readyToClose: (bool) $entity->readyToClose,
        );
    }
}
