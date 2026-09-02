<?php

declare(strict_types=1);

namespace App\AI\Factory\Quality;

use App\AI\Dto\Quality\CorrectivePreventiveActionModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use LegacyBundle\Entity\Quality\CorrectivePreventiveAction;

final readonly class CorrectivePreventiveActionModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return CorrectivePreventiveAction::class === $class;
    }

    /**
     * @param CorrectivePreventiveAction $entity
     */
    public function create(object $entity): CorrectivePreventiveActionModel
    {
        return new CorrectivePreventiveActionModel(
            shortDescription: $entity->shortDescription,
            description: $entity->description,
            department: $entity->department,
            type: $entity->type,
            status: $entity->status,
            lastStatus: $entity->lastStatus,
            openedDate: $entity->openedDate,
            targetDate: $entity->targetDate,
            closedDate: $entity->closedDate,
            suspendedDate: $entity->suspendedDate,
            daysSuspended: $entity->daysSuspended,
            containmentAction: $entity->containmentAction,
            rootCause: $entity->rootCause,
            correctiveAction: $entity->correctiveAction,
            preventiveAction: $entity->preventiveAction,
            resolution: $entity->resolution,
            rejectionReason: $entity->rejectionReason,
            importanceFactor: $entity->importanceFactor,
            finalWeight: $entity->finalWeight,
            verificationDescription: $entity->verificationDescription,
            location: null === $entity->location ? null : $this->locationModelFactory->create($entity->location),
            projectLeader: null === $entity->projectLeader ? null : $this->peopleModelFactory->create($entity->projectLeader),
            poster: null === $entity->poster ? null : $this->peopleModelFactory->create($entity->poster),
            initiator: null === $entity->initiator ? null : $this->peopleModelFactory->create($entity->initiator),
        );
    }
}
