<?php

declare(strict_types=1);

namespace App\AI\Factory\Engineering;

use App\AI\Dto\Engineering\EngineeringActivityProcessModel;
use App\AI\Dto\Engineering\EngineeringActivityProcessPartModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use LegacyBundle\Entity\Engineering\EngineeringActivityProcess;
use LegacyBundle\Entity\Engineering\EngineeringActivityProcessPart;

final readonly class EngineeringActivityProcessModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private MasterEngineeringActivityProcessModelFactory $masterFactory,
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return EngineeringActivityProcess::class === $class;
    }

    /**
     * @param EngineeringActivityProcess $entity
     */
    public function create(object $entity): EngineeringActivityProcessModel
    {
        return new EngineeringActivityProcessModel(
            shortDescription: $entity->shortDescription,
            description: $entity->description,
            status: $entity->status,
            openedAt: $entity->openedAt,
            closedAt: $entity->closedAt,
            category: $entity->category,
            importanceFactor: $entity->importanceFactor,
            type: $entity->type,
            model: $entity->model,
            actionPlan: $entity->actionPlan,
            currency: $entity->currency,
            additionalInformation: $entity->additionalInformation,
            expectedHours: $entity->expectedHours,
            master: null === $entity->masterEngineeringActivityProcess
                ? null
                : $this->masterFactory->create($entity->masterEngineeringActivityProcess),
            factory: null === $entity->factory ? null : $this->locationModelFactory->create($entity->factory),
            reportedBy: null === $entity->reportedBy ? null : $this->peopleModelFactory->create($entity->reportedBy),
            poster: null === $entity->poster ? null : $this->peopleModelFactory->create($entity->poster),
            assignee: null === $entity->assignee ? null : $this->peopleModelFactory->create($entity->assignee),
            parts: array_values(array_map(
                static fn (EngineeringActivityProcessPart $part) => new EngineeringActivityProcessPartModel(
                    partNumber: $part->partNumber,
                ),
                $entity->parts->toArray(),
            )),
        );
    }
}
