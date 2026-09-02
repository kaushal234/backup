<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Engineering;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\MasterEngineeringActivityProcess;

final readonly class MasterEngineeringActivityProcessFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'master_engineering_activity_process';
    }

    public function entityClass(): string
    {
        return MasterEngineeringActivityProcess::class;
    }

    public function defaultOrder(): array
    {
        return ['lifecycleDates.createdOn' => 'DESC'];
    }

    public function description(): string
    {
        return 'Master Engineering Activity Processes (MEAP) — top-level engineering projects/programs raised against a GSE product model, progressing through a phase/gate lifecycle, with their status, product type, purpose, importance factor (ifactor), factory, poster, project leader, descriptions, economics and creation/closing dates. Groups the child Engineering Activity Processes (EAP) linked to it.';
    }

    public function fields(): array
    {
        $statuses = [
            'PROPOSAL', 'GATE_PROPOSAL',
            'PHASE_0', 'GATE_0', 'PHASE_1', 'GATE_1', 'PHASE_2', 'GATE_2',
            'PHASE_3', 'GATE_3', 'PHASE_4', 'GATE_4',
            'SUSPENDED', 'REJECTED', 'CLOSED',
        ];

        return [
            Filter::in('statuses', 'status', enum: $statuses, desc: 'MEAP statuses, following the phase/gate lifecycle. Open/in-progress = PROPOSAL, GATE_PROPOSAL, PHASE_0..4, GATE_0..4, SUSPENDED; terminal = REJECTED, CLOSED. Multiple = OR.'),
            Filter::in('productTypes', 'productType', desc: 'Exact product types (GSE product family). Multiple = OR.'),
            Filter::in('types', 'type', desc: 'Exact MEAP types. Often empty. Multiple = OR.'),
            Filter::in('purposes', 'purpose', desc: 'Exact MEAP purposes. Multiple = OR.'),
            Filter::like('modelLike', 'model', desc: 'Partial product model (LIKE %value%).'),
            Filter::like('shortDescriptionLike', 'shortDescription', desc: 'Partial short description / title (LIKE %value%).'),
            Filter::like('descriptionLike', 'description', desc: 'Partial full description (LIKE %value%).'),
            Filter::inInt('importanceFactors', 'scoring.importanceFactor', desc: 'Importance factors (ifactor, severity weight). Multiple = OR.'),
            Filter::in('factoryNames', 'factory.name', desc: 'Exact factory / location names. Multiple = OR.'),
            Filter::inInt('posterIds', 'poster', desc: 'IDs of the people who posted the MEAP.'),
            Filter::inInt('projectLeaderIds', 'projectLeader', desc: 'IDs of the project leaders (People).'),
            Filter::bool('isPrivate', 'isPrivate', trueValue: 'Y', falseValue: 'N', desc: 'True = only private MEAPs, false = only public ones, omit for both.'),
            Filter::exists('isClosed', 'lifecycleDates.closedOn', desc: 'True = only closed MEAPs (with a closing date), false = only open ones (no closing date), omit for both.'),
            Filter::dateRange('lifecycleDates.createdOn', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — MEAPs created on/after this date.', beforeDesc: 'ISO-8601 date — MEAPs created on/before this date.'),
            Filter::dateRange('lifecycleDates.closedOn', afterName: 'closedAfter', beforeName: 'closedBefore', afterDesc: 'ISO-8601 date — MEAPs closed on/after this date.', beforeDesc: 'ISO-8601 date — MEAPs closed on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, MasterEngineeringActivityProcess::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->status,
            'productType' => $entity->productType,
            'type' => $entity->type,
            'model' => $entity->model,
            'purpose' => '' !== $entity->purpose ? $entity->purpose : null,
            'shortDescription' => $entity->shortDescription,
            'importanceFactor' => $entity->scoring->importanceFactor,
            'isPrivate' => 'Y' === $entity->isPrivate,
            'factory' => $entity->factory?->getName(),
            'poster' => $this->legacyPersonName($entity->poster),
            'projectLeader' => $this->legacyPersonName($entity->projectLeader),
            'createdOn' => $entity->lifecycleDates->createdOn?->format(\DATE_ATOM),
            'closedOn' => $entity->lifecycleDates->closedOn?->format(\DATE_ATOM),
        ];
    }
}
