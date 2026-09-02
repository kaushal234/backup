<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Task;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\Entity\BaseTask;
use App\Entity\Task\Task;

final readonly class TaskFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'task';
    }

    public function entityClass(): string
    {
        return Task::class;
    }

    public function defaultOrder(): array
    {
        return ['createdAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'Tasks (to-do / action items / assignments) raised on a module, including status, indice factor, module, creator, assignee, recipients, confidentiality, reference id and dates (created, due, started, closed). ';
    }

    public function fields(): array
    {
        $statuses = [BaseTask::PENDING, BaseTask::IN_PROGRESS, Task::CLOSED, Task::PAUSE];
        $indiceFactors = [BaseTask::IF_1, BaseTask::IF_10, BaseTask::IF_100, BaseTask::IF_1000, BaseTask::IF_10000];

        return [
            Filter::like('shortDescriptionLike', 'shortDescription', desc: 'Partial, case-insensitive match on the task short description.'),
            Filter::like('descriptionLike', 'description', desc: 'Partial, case-insensitive match on the task description.'),
            Filter::in('statuses', 'status', enum: $statuses, desc: 'Task statuses. Multiple = OR.'),
            Filter::in('indiceFactors', 'indiceFactor', enum: $indiceFactors, desc: 'Indice factors. Multiple = OR.'),
            Filter::in('moduleNames', 'module.name', desc: 'Exact module names.'),
            Filter::inInt('createdByPeopleIds', 'createdBy', desc: 'IDs of creators (People).'),
            Filter::inInt('assigneePeopleIds', 'assignee', desc: 'IDs of assignees (People).'),
            Filter::inInt('recipientPeopleIds', 'recipients.id', desc: 'IDs of recipients in copy (People).'),
            Filter::eqInt('referenceId', 'referenceId', desc: 'Exact reference id of the related entity.'),
            Filter::bool('confidential', 'confidential', desc: 'True = only confidential tasks, false = only non-confidential, omit for both.'),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — tasks created on/after this date.', beforeDesc: 'ISO-8601 date — tasks created on/before this date.'),
            Filter::dateRange('dueDate', afterName: 'dueAfter', beforeName: 'dueBefore', afterDesc: 'ISO-8601 date — tasks due on/after this date.', beforeDesc: 'ISO-8601 date — tasks due on/before this date.'),
            Filter::dateRange('startedAt', afterName: 'startedAfter', beforeName: 'startedBefore', afterDesc: 'ISO-8601 date — tasks started on/after this date.', beforeDesc: 'ISO-8601 date — tasks started on/before this date.'),
            Filter::dateRange('closedAt', afterName: 'closedAfter', beforeName: 'closedBefore', afterDesc: 'ISO-8601 date — tasks closed on/after this date.', beforeDesc: 'ISO-8601 date — tasks closed on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, Task::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->getStatus(),
            'shortDescription' => $entity->shortDescription,
            'indiceFactor' => $entity->indiceFactor,
            'module' => $entity->module?->getName(),
            'referenceId' => $entity->referenceId,
            'confidential' => $entity->confidential,
            'createdBy' => $this->personName($entity->createdBy),
            'assignee' => $this->personName($entity->assignee),
            'createdAt' => $entity->createdAt->format(\DATE_ATOM),
            'startedAt' => $entity->startedAt?->format(\DATE_ATOM),
            'dueDate' => $entity->dueDate?->format(\DATE_ATOM),
            'closedAt' => $entity->closedAt?->format(\DATE_ATOM),
            'rescheduleDate' => $entity->rescheduleDate?->format(\DATE_ATOM),
        ];
    }
}
