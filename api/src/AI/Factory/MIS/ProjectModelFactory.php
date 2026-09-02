<?php

declare(strict_types=1);

namespace App\AI\Factory\MIS;

use App\AI\Dto\MIS\Project\PhaseModel;
use App\AI\Dto\MIS\Project\ProjectModel;
use App\AI\Dto\Module\ModuleModel;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use App\Entity\Directory\People;
use App\Entity\MIS\Project\Phase;
use App\Entity\MIS\Project\Project;
use App\Entity\MIS\Project\ProjectTag;

final readonly class ProjectModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return Project::class === $class;
    }

    /**
     * @param Project $entity
     */
    public function create(object $entity): ProjectModel
    {
        return new ProjectModel(
            name: $entity->name,
            description: $entity->description,
            indicesFactor: $entity->indicesFactor,
            status: $entity->getStatus(),
            confidential: $entity->confidential,
            createdAt: $entity->createdAt,
            startedAt: $entity->startedAt,
            lastCommentedAt: $entity->lastCommentedAt,
            lastComment: $entity->lastComment,
            conclusion: $entity->conclusion,
            teamsLink: $entity->teamsLink,
            region: $entity->region?->getName(),
            module: null === $entity->module ? null : new ModuleModel(name: $entity->module->getName()),
            projectManager: null === $entity->projectManager ? null : $this->peopleModelFactory->create($entity->projectManager),
            misOwner: null === $entity->misOwner ? null : $this->peopleModelFactory->create($entity->misOwner),
            moduleKeyUsers: array_values(array_map(
                fn (People $people) => $this->peopleModelFactory->create($people),
                $entity->getModuleKeyUsers()->toArray(),
            )),
            misMembers: array_values(array_map(
                fn (People $people) => $this->peopleModelFactory->create($people),
                $entity->getMisMembers()->toArray(),
            )),
            phases: array_values(array_map(
                static fn (Phase $phase) => new PhaseModel(
                    number: $phase->number,
                    estimatedClosureAt: $phase->estimatedClosureAt,
                    revisedClosureAt: $phase->revisedClosureAt,
                    estimatedHours: $phase->estimatedHours,
                    revisedEstimatedHours: $phase->revisedEstimatedHours,
                ),
                $entity->getPhases()->toArray(),
            )),
            tags: array_values(array_map(
                static fn (ProjectTag $tag) => $tag->getName(),
                $entity->getTags()->toArray(),
            )),
        );
    }
}
