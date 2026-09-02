<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Service;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;

final readonly class TocFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'technician_on_call';
    }

    public function entityClass(): string
    {
        return TechnicianOnCall::class;
    }

    public function defaultOrder(): array
    {
        return ['createdAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'Technician On Call records (TOC) — field service interventions/calls handled by a technician for a customer, with their status, type, activity type, importance factor (ifactor), unit operation status, service organization, airport, the technician/assignee/creator/customer involved and the open/close dates. ';
    }

    public function fields(): array
    {
        $statuses = [
            TechnicianOnCall::PENDING,
            TechnicianOnCall::IN_PROGRESS,
            TechnicianOnCall::SOLVED,
            TechnicianOnCall::CLOSED,
            TechnicianOnCall::SUSPENDED,
        ];
        $types = ['Not Defined Yet', 'Warranty', 'Payable Services', 'SSO', 'Factory', 'SSO Sales Concession'];
        $activityTypes = ['Troubleshooting', 'Maintenance', 'Commissioning', 'Service Bulletin', 'Unit Upgrade', 'Training'];
        $unitOperationStatuses = ['MCF', 'NMC', 'MCP', 'FMC'];

        return [
            Filter::in('statuses', 'status', enum: $statuses, desc: 'TOC statuses. Open/in-progress = PENDING, IN_PROGRESS, SUSPENDED; terminal = CLOSED, SOLVED. Multiple = OR.'),
            Filter::in('types', 'technicianOnCallType.name', enum: $types, desc: 'TOC types. Multiple = OR.'),
            Filter::in('activityTypes', 'serviceActivity.name', enum: $activityTypes, desc: 'Activity types. Multiple = OR.'),
            Filter::in('importanceFactors', 'indiceFactor', enum: ['IF 1', 'IF 10', 'IF 100', 'IF 1000'], desc: 'Importance factors (ifactor, severity weight): "IF 1", "IF 10", "IF 100" or "IF 1000". Multiple = OR.'),
            Filter::in('unitOperationStatuses', 'unitOperationalStatus.name', enum: $unitOperationStatuses, desc: 'Unit operation statuses (MCF, NMC, MCP, FMC). Multiple = OR.'),
            Filter::in('serviceOrganizationLocationNames', 'salesOrganisationService.name', desc: 'Exact service organization location names. Multiple = OR.'),
            Filter::in('airportCodes', 'airport.code', desc: 'Exact airport codes (APC). Multiple = OR.'),
            Filter::in('equipmentTypes', 'equipmentRecord.type', desc: 'Exact equipment types of the serviced equipment record. Multiple = OR.'),
            Filter::in('equipmentModels', 'equipmentRecord.model', desc: 'Exact equipment models of the serviced equipment record. Multiple = OR.'),
            Filter::inInt('technicianPeopleIds', 'technician', desc: 'IDs of the technicians (People).'),
            Filter::inInt('assigneePeopleIds', 'assignee', desc: 'IDs of the assignees (People).'),
            Filter::inInt('posterPeopleIds', 'createdBy', desc: 'IDs of the creators (People).'),
            Filter::in('customerNames', 'customer.name', desc: 'Exact customer names. Multiple = OR.'),
            Filter::exists('isClosed', 'solvedAt', desc: 'True = only closed (has a close date), false = only open, omit for both.'),
            Filter::bool('isFactorySupportRequired', 'factoryFlag', desc: 'Whether factory support is required.'),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — TOCs created on/after this date.', beforeDesc: 'ISO-8601 date — TOCs created on/before this date.'),
            Filter::dateRange('solvedAt', afterName: 'closedAfter', beforeName: 'closedBefore', afterDesc: 'ISO-8601 date — TOCs closed on/after this date.', beforeDesc: 'ISO-8601 date — TOCs closed on/before this date.'),
            Filter::like('errorCodes', 'errorCodes', 'Error codes of the TOC'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, TechnicianOnCall::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->status,
            'type' => isset($entity->technicianOnCallType) ? $entity->technicianOnCallType->name : null,
            'activityType' => $entity->serviceActivity->name,
            'importanceFactor' => $entity->indiceFactor,
            'unitOperationStatus' => $this->safeAccess(static fn (): ?string => $entity->unitOperationalStatus?->getName()),
            'title' => $entity->title,
            'serviceOrganization' => $this->safeAccess(static fn (): ?string => $entity->salesOrganisationService?->getName()),
            'airportCode' => $this->safeAccess(static fn (): ?string => isset($entity->airport) ? $entity->airport->getCode() : null),
            'equipmentType' => $this->safeAccess(static fn (): ?string => $entity->equipmentRecord?->getType()),
            'equipmentModel' => $this->safeAccess(static fn (): ?string => $entity->equipmentRecord?->getModel()),
            'technician' => $this->personName($entity->technician),
            'assignee' => $this->personName($entity->assignee),
            'createdBy' => $this->personName($entity->createdBy instanceof People ? $entity->createdBy : null),
            'customer' => $this->safeAccess(static fn (): ?string => $entity->customer?->getName()),
            'isFactorySupportRequired' => $entity->factoryFlag,
            'createdAt' => $entity->createdAt->format(\DATE_ATOM),
            'closedAt' => $entity->solvedAt?->format(\DATE_ATOM),
            'errorCodes' => $entity->errorCodes,
        ];
    }
}
