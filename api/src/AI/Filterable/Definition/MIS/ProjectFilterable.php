<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\MIS;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\Entity\MIS\Project\Project;
use App\Entity\MIS\Project\ProjectTag;

final readonly class ProjectFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'mis_project';
    }

    public function entityClass(): string
    {
        return Project::class;
    }

    public function defaultOrder(): array
    {
        return ['createdAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'MIS projects (information-systems / module improvement projects), including status, phase, indices factor, region, module, project manager, MIS owner, members and tags. ';
    }

    public function fields(): array
    {
        $statuses = [
            Project::PENDING, Project::PHASE_0, Project::PHASE_1, Project::PHASE_2,
            Project::PHASE_3, Project::PHASE_4, Project::CANCELLED, Project::CLOSED,
        ];

        return [
            Filter::like('nameLike', 'name', desc: 'Partial, case-insensitive match on the project name.'),
            Filter::in('statuses', 'status', enum: $statuses, desc: 'Project statuses (also the current phase). Multiple = OR.'),
            Filter::in('indicesFactors', 'indicesFactor', enum: Project::INDICES_FACTOR, desc: 'Project indices factors. Multiple = OR.'),
            Filter::in('regionNames', 'region.name', desc: 'Exact region names.'),
            Filter::in('moduleNames', 'module.name', desc: 'Exact module names.'),
            Filter::inInt('projectManagerPeopleIds', 'projectManager', desc: 'IDs of project managers (People).'),
            Filter::inInt('misOwnerPeopleIds', 'misOwner', desc: 'IDs of MIS owners (People).'),
            Filter::inInt('memberPeopleIds', 'misMembers.id', desc: 'IDs of MIS members (People) assigned to the project.'),
            Filter::in('tagNames', 'tags.name', desc: 'Exact project tag names.'),
            Filter::bool('confidential', 'confidential', desc: 'True = only confidential projects, false = only non-confidential, omit for both.'),
            Filter::dateRange('startedAt', afterName: 'startedAfter', beforeName: 'startedBefore', afterDesc: 'ISO-8601 date — projects started on/after this date.', beforeDesc: 'ISO-8601 date — projects started on/before this date.'),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — projects created on/after this date.', beforeDesc: 'ISO-8601 date — projects created on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, Project::class);

        return [
            'id' => $entity->getId(),
            'name' => $entity->name,
            'status' => $entity->getStatus(),
            'indicesFactor' => $entity->indicesFactor,
            'region' => $entity->region?->getName(),
            'module' => $entity->module?->getName(),
            'projectManager' => $this->personName($entity->projectManager),
            'misOwner' => $this->personName($entity->misOwner),
            'confidential' => $entity->confidential,
            'tags' => array_values(array_map(
                static fn (ProjectTag $tag) => $tag->getName(),
                $entity->getTags()->toArray(),
            )),
            'activePhase' => $entity->getActivePhase()?->number,
            'dueDate' => $entity->getDueDate(),
            'createdAt' => $entity->createdAt->format(\DATE_ATOM),
            'startedAt' => $entity->startedAt->format(\DATE_ATOM),
        ];
    }
}
