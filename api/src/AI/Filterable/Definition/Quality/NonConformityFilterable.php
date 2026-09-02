<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Quality;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\Entity\Parts\NonConformityPart;
use App\Entity\Quality\NonConformity;
use App\Entity\Quality\Process;
use App\Entity\Quality\Responsible;

final readonly class NonConformityFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'non_conformity';
    }

    public function entityClass(): string
    {
        return NonConformity::class;
    }

    public function defaultOrder(): array
    {
        return ['createdAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'Non-conformity reports (NCR), also known as non-conformance reports or quality non-conformities. ';
    }

    public function fields(): array
    {
        $statuses = [
            NonConformity::PENDING, NonConformity::IN_PROGRESS, NonConformity::SUSPENDED,
            NonConformity::REJECTED, NonConformity::CLOSED,
        ];
        $failureTypes = [
            NonConformity::ELECTRICAL, NonConformity::MECHANICAL, NonConformity::WELDMENT,
            NonConformity::ENGINE_POWER_TRAIN, NonConformity::ADMINISTRATION,
            NonConformity::HYDRAULIC, NonConformity::PAINT, NonConformity::SURFACE_COATING,
        ];
        $processCategories = [
            Process::ENGINEERING, Process::PRODUCTION, Process::PURCHASING,
            Process::WAREHOUSE, Process::OTHERS,
        ];
        $responsibleNames = [Responsible::TLD, Responsible::SUPPLIER, Responsible::CUSTOMER];

        return [
            Filter::in('statuses', 'status', enum: $statuses, desc: 'NCR statuses. Multiple = OR.'),
            Filter::in('failureTypes', 'failureType', enum: $failureTypes, desc: 'Failure types. Multiple = OR.'),
            Filter::in('iFactors', 'iFactor', desc: 'Importance factors (severity), e.g. "IF1", "IF10", "IF100", "IF1000". Multiple = OR.'),
            Filter::like('shortDescriptionLike', 'shortDescription', desc: 'Partial short description / title (LIKE %value%).'),
            Filter::like('problemLike', 'problem', desc: 'Partial problem description (LIKE %value%).'),
            Filter::in('supplierNumbers', 'supplierNumber', desc: 'Exact supplier numbers (business partner codes).'),
            Filter::like('supplierNameLike', 'supplierName', desc: 'Partial supplier name (LIKE %value%).'),
            Filter::in('partNumbers', 'parts.referenceNumber', desc: 'Exact part numbers (reference numbers) attached to the NCR.'),
            Filter::in('serialNumbers', 'parts.serialNumber', desc: 'Exact part serial numbers attached to the NCR.'),
            Filter::in('locationNames', 'location.name', desc: 'Exact location / factory names.'),
            Filter::in('processCategories', 'processes.category', enum: $processCategories, desc: 'Process categories. Multiple = OR.'),
            Filter::in('responsibleNames', 'responsibles.name', enum: $responsibleNames, desc: 'Responsibility (who is responsible). Multiple = OR.'),
            Filter::inInt('reportedByPeopleIds', 'reportedBy', desc: 'IDs of reporters (People).'),
            Filter::bool('safety', 'safety', desc: 'True = only safety-related NCRs, false = non-safety, omit for both.'),
            Filter::bool('environmentalIssue', 'environmentalIssue', desc: 'True = only environmental-issue NCRs, false = without, omit for both.'),
            Filter::bool('rush', 'rush', desc: 'True = only rush NCRs, false = non-rush, omit for both.'),
            Filter::exists('isClosed', 'closedAt', desc: 'True = only closed, false = only open, omit for both.'),
            Filter::hasMany('hasVendorWarrantyClaim', 'vendorWarrantyClaims', desc: 'True = only NCRs linked to a vendor warranty claim (VWC), false = without, omit for both.'),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — NCRs created on/after this date.', beforeDesc: 'ISO-8601 date — NCRs created on/before this date.'),
            Filter::dateRange('closedAt', afterName: 'closedAfter', beforeName: 'closedBefore', afterDesc: 'ISO-8601 date — NCRs closed on/after this date.', beforeDesc: 'ISO-8601 date — NCRs closed on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, NonConformity::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->status,
            'iFactor' => $entity->iFactor,
            'shortDescription' => $entity->shortDescription,
            'failureType' => $entity->failureType,
            'supplierNumber' => $entity->getSupplierNumber(),
            'supplierName' => $entity->getSupplierName(),
            'location' => $entity->location->getName(),
            'reportedBy' => $this->personName($entity->reportedBy),
            'safety' => $entity->safety,
            'environmentalIssue' => $entity->environmentalIssue,
            'rush' => $entity->rush,
            'processes' => array_values(array_map(
                static fn (Process $process): string => $process->category,
                $entity->getProcesses()->toArray(),
            )),
            'responsibles' => array_values(array_map(
                static fn (Responsible $responsible): string => $responsible->name,
                $entity->getResponsibles()->toArray(),
            )),
            'partNumbers' => array_values(array_filter(array_map(
                static fn (NonConformityPart $part): ?string => $part->referenceNumber,
                $entity->getParts()->toArray(),
            ))),
            'vendorWarrantyClaimCount' => $entity->getVendorWarrantyClaims()->count(),
            'createdAt' => $entity->createdAt->format(\DATE_ATOM),
            'closedAt' => $entity->closedAt?->format(\DATE_ATOM),
        ];
    }
}
