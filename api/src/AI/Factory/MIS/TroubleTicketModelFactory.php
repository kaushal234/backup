<?php

declare(strict_types=1);

namespace App\AI\Factory\MIS;

use App\AI\Dto\MIS\TroubleTicket\TroubleTicketModel;
use App\AI\Dto\MIS\TroubleTicket\TypeModel;
use App\AI\Dto\Module\ModuleModel;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\MIS\TroubleTicket\Type;
use App\Entity\Module\Module;

final readonly class TroubleTicketModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return TroubleTicket::class === $class;
    }

    /**
     * @param TroubleTicket $entity
     */
    public function create(object $entity): TroubleTicketModel
    {
        return new TroubleTicketModel(
            status: $entity->getStatus(),
            shortDescription: $entity->shortDescription,
            description: $entity->description,
            indiceFactor: $entity->indiceFactor,
            jiraIssueNumber: $entity->jiraIssueNumber,
            url: $entity->url,
            referer: $entity->referer,
            hostName: $entity->hostName,
            satisfaction: $entity->satisfaction,
            isAddToUserStories: $entity->isAddToUserStories,
            createdAt: $entity->createdAt,
            dueDate: $entity->dueDate,
            closedAt: $entity->closedAt,
            solutionProposedAt: $entity->solutionProposedAt,
            lastCommentedAt: $entity->lastCommentedAt,
            module: null === $entity->module ? null : $this->createModule($entity->module),
            type: $this->createType($entity->type),
            createdBy: null === $entity->createdBy ? null : $this->peopleModelFactory->create($entity->createdBy),
            assignee: null === $entity->assignee ? null : $this->peopleModelFactory->create($entity->assignee),
            misAssignee: null === $entity->misAssignee ? null : $this->peopleModelFactory->create($entity->misAssignee),
            ccs: array_values(array_map(
                fn (People $people) => $this->peopleModelFactory->create($people),
                $entity->getCcs()->toArray(),
            )),
            additionalOwners: array_values(array_map(
                fn (People $people) => $this->peopleModelFactory->create($people),
                $entity->getAdditionalOwners()->toArray(),
            )),
        );
    }

    private function createModule(Module $module): ModuleModel
    {
        return new ModuleModel(name: $module->getName());
    }

    private function createType(Type $type): TypeModel
    {
        return new TypeModel(
            type: $type->type,
            description: $type->description,
            indiceFactor: $type->indiceFactor,
        );
    }
}
