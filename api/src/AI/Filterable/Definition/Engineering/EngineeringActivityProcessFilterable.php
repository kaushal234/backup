<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Engineering;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use LegacyBundle\Entity\Engineering\EngineeringActivityProcess;

final readonly class EngineeringActivityProcessFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'engineering_activity_process';
    }

    public function entityClass(): string
    {
        return EngineeringActivityProcess::class;
    }

    public function defaultOrder(): array
    {
        return ['openedAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'Engineering Activity Processes (EAP) — engineering activities/tasks raised against a GSE product model, with their status, category, type, importance factor (ifactor), factory, reporter/poster/assignee, action plan, expected hours, opening and closing dates. May be linked to a parent Master EAP (MEAP).';
    }

    public function fields(): array
    {
        $statuses = ['PENDING', 'IN QUEUE', 'IN PROGRESS', 'NOTIFICATION', 'PROPOSED', 'REJECTED', 'CLOSED'];
        $categories = ['Engineering Change', 'Engineering Development', 'Engineering Information Request', 'Risk Assessment', 'Technical data', 'FAQP', 'Project Goal Change'];

        return [
            Filter::in('statuses', 'status', enum: $statuses, desc: 'EAP statuses. Open = PENDING, IN QUEUE, IN PROGRESS, NOTIFICATION, PROPOSED; terminal = REJECTED, CLOSED. Multiple = OR.'),
            Filter::in('categories', 'category', enum: $categories, desc: 'EAP categories. Multiple = OR.'),
            Filter::in('types', 'type', desc: 'Exact EAP types (GSE product family, e.g. "Towbarless Aircraft Tractors", "Belt Loaders"). Often empty. Multiple = OR.'),
            Filter::like('modelLike', 'model', desc: 'Partial product model (LIKE %value%).'),
            Filter::like('shortDescriptionLike', 'shortDescription', desc: 'Partial short description / title (LIKE %value%).'),
            Filter::like('descriptionLike', 'description', desc: 'Partial full description (LIKE %value%).'),
            Filter::in('importanceFactors', 'importanceFactor', desc: 'Importance factors (ifactor, severity weight). Multiple = OR.'),
            Filter::in('factoryNames', 'factory.name', desc: 'Exact factory / location names. Multiple = OR.'),
            Filter::inInt('reporterIds', 'reportedBy', desc: 'IDs of the people who reported the EAP.'),
            Filter::inInt('posterIds', 'poster', desc: 'IDs of the people who posted the EAP.'),
            Filter::inInt('assigneeIds', 'assignee', desc: 'IDs of the assignees (People).'),
            Filter::exists('isClosed', 'closedAt', desc: 'True = only closed EAPs (with a closing date), false = only open ones (no closing date), omit for both.'),
            Filter::dateRange('openedAt', afterName: 'openedAfter', beforeName: 'openedBefore', afterDesc: 'ISO-8601 date — EAPs opened on/after this date.', beforeDesc: 'ISO-8601 date — EAPs opened on/before this date.'),
            Filter::dateRange('closedAt', afterName: 'closedAfter', beforeName: 'closedBefore', afterDesc: 'ISO-8601 date — EAPs closed on/after this date.', beforeDesc: 'ISO-8601 date — EAPs closed on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, EngineeringActivityProcess::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->status,
            'category' => $entity->category,
            'type' => $entity->type,
            'model' => $entity->model,
            'shortDescription' => $entity->shortDescription,
            'importanceFactor' => '' !== $entity->importanceFactor ? $entity->importanceFactor : null,
            'expectedHours' => $entity->expectedHours,
            'factory' => $entity->factory?->getName(),
            'masterId' => $entity->masterEngineeringActivityProcess?->getId(),
            'reportedBy' => $this->legacyPersonName($entity->reportedBy),
            'poster' => $this->legacyPersonName($entity->poster),
            'assignee' => $this->legacyPersonName($entity->assignee),
            'openedAt' => $entity->openedAt->format(\DATE_ATOM),
            'closedAt' => $entity->closedAt?->format(\DATE_ATOM),
        ];
    }
}
