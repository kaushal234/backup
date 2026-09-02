<?php

declare(strict_types=1);

namespace App\AI\Factory\Service;

use App\AI\Dto\Service\ServiceBulletinModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use LegacyBundle\Entity\ServiceBulletin;

final readonly class ServiceBulletinModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return ServiceBulletin::class === $class;
    }

    /**
     * @param ServiceBulletin $entity
     */
    public function create(object $entity): ServiceBulletinModel
    {
        return new ServiceBulletinModel(
            parentId: $entity->parentId,
            status: $entity->status,
            category: $entity->category,
            categoryReason: $entity->categoryReason,
            type: $entity->type,
            importanceFactor: $entity->importanceFactor,
            confidential: $entity->confidential,
            title: $entity->title,
            description: $entity->description,
            laborHours: $entity->laborHours,
            numberOfTechniciansNeeded: $entity->numberOfTechniciansNeeded,
            partsNeeded: $entity->isPartsNeeded(),
            factoryPartAvailabilityStatus: $entity->factoryPartAvailabilityStatus,
            createdAt: $entity->createdAt,
            ssdApprovedAt: $entity->ssdApprovedAt,
            ssdDecidedAt: $entity->ssdDecidedAt,
            implementedAt: $entity->implementedAt,
            closedAt: $entity->closedAt,
            poster: null === $entity->poster ? null : $this->peopleModelFactory->create($entity->poster),
            factory: null === $entity->factory ? null : $this->locationModelFactory->create($entity->factory),
        );
    }
}
