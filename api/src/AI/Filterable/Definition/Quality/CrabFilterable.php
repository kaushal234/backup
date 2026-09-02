<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Quality;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\Entity\Quality\Crab;

final readonly class CrabFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'crab';
    }

    public function entityClass(): string
    {
        return Crab::class;
    }

    public function defaultOrder(): array
    {
        return ['createdAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'CRAB quality records (corrective/inspection records on equipment), including status, category, defect code, and linked part. ';
    }

    public function fields(): array
    {
        $statuses = [Crab::CLOSED, Crab::TO_FIX, Crab::TO_INSPECT, Crab::FOR_DEROGATION];
        $categories = [Crab::ASSY, Crab::PDI, Crab::QA, Crab::TEST, Crab::PDI_CSC, Crab::PDI_SOL, Crab::PDI_INTERNAL];
        $piQuestionTypes = [Crab::ECQ, Crab::PCQ, Crab::QCQ];

        return [
            Filter::in('statuses', 'status', enum: $statuses, desc: 'CRAB statuses. Multiple = OR.'),
            Filter::in('categories', 'category', enum: $categories, desc: 'Operation categories. Multiple = OR.'),
            Filter::in('departmentNames', 'department.name', desc: 'Exact CRAB department names.'),
            Filter::inInt('codes', 'code.code', desc: 'Exact CRAB defect codes (numeric).'),
            Filter::in('partNumbers', 'part.partNumber', desc: 'Exact part numbers attached to the CRAB.'),
            Filter::in('equipmentSerialNumbers', 'equipmentRecord.serialNumber', desc: 'Exact equipment record serial numbers.'),
            Filter::inInt('createdByPeopleIds', 'createdBy', desc: 'IDs of creators (People).'),
            Filter::inInt('fixedByPeopleIds', 'fixedBy', desc: 'IDs of people who fixed the CRAB (People).'),
            Filter::inInt('inspectedByPeopleIds', 'inspectedBy', desc: 'IDs of inspectors (People).'),
            Filter::in('piQuestionTypes', 'piQuestionType', enum: $piQuestionTypes, desc: 'PI question types. Multiple = OR.'),
            Filter::exists('hasDerogation', 'derogation', desc: 'True = only CRABs with a derogation, false = without, omit for both.'),
            Filter::exists('hasNonConformity', 'nonConformity', desc: 'True = only CRABs linked to a non-conformity, false = without, omit for both.'),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — CRABs created on/after this date.', beforeDesc: 'ISO-8601 date — CRABs created on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, Crab::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->status,
            'category' => $entity->category,
            'department' => $entity->department->name,
            'code' => $entity->code?->code,
            'codeDescription' => $entity->code?->description,
            'partNumber' => $entity->getPart()?->partNumber,
            'equipmentSerialNumber' => $entity->equipmentRecord?->getSerialNumber(),
            'description' => $entity->description,
            'createdBy' => $this->personName($entity->createdBy),
            'fixedBy' => $this->personName($entity->fixedBy),
            'inspectedBy' => $this->personName($entity->inspectedBy),
            'piQuestionType' => $entity->piQuestionType,
            'hasDerogation' => null !== $entity->derogation,
            'nonConformityId' => $entity->nonConformity?->getId(),
            'createdAt' => $entity->createdAt->format(\DATE_ATOM),
            'fixedAt' => $entity->fixedAt?->format(\DATE_ATOM),
            'inspectedAt' => $entity->inspectedAt?->format(\DATE_ATOM),
        ];
    }
}
