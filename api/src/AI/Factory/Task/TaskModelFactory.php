<?php

declare(strict_types=1);

namespace App\AI\Factory\Task;

use App\AI\Dto\Module\ModuleModel;
use App\AI\Dto\Task\TaskModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use App\Entity\Directory\People;
use App\Entity\Task\Task;

final readonly class TaskModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return Task::class === $class;
    }

    /**
     * @param Task $entity
     */
    public function create(object $entity): TaskModel
    {
        return new TaskModel(
            status: $entity->getStatus(),
            confidential: $entity->confidential,
            indiceFactor: $entity->indiceFactor,
            referenceId: $entity->referenceId,
            shortDescription: $entity->shortDescription,
            description: $entity->description,
            createdAt: $entity->createdAt,
            startedAt: $entity->startedAt,
            dueDate: $entity->dueDate,
            rescheduleDate: $entity->rescheduleDate,
            closedAt: $entity->closedAt,
            escalationDate: $entity->escalationDate,
            escalationTrigger: $entity->escalationTrigger,
            escalationTriggerUnit: $entity->escalationTriggerUnit,
            lastComment: $entity->lastComment,
            closeComment: $entity->closeComment,
            module: null === $entity->module ? null : new ModuleModel(name: $entity->module->getName()),
            createdBy: null === $entity->createdBy ? null : $this->peopleModelFactory->create($entity->createdBy),
            assignee: null === $entity->assignee ? null : $this->peopleModelFactory->create($entity->assignee),
            location: $this->locationModelFactory->create($entity->location),
            recipients: array_values(array_map(
                fn (People $people) => $this->peopleModelFactory->create($people),
                $entity->getRecipients()->toArray(),
            )),
        );
    }
}
