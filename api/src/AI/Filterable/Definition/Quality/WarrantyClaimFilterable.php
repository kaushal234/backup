<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Quality;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use LegacyBundle\Entity\Quality\WarrantyClaim;

final readonly class WarrantyClaimFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'warranty_claim';
    }

    public function entityClass(): string
    {
        return WarrantyClaim::class;
    }

    public function defaultAlias(): string
    {
        return 'wc';
    }

    public function defaultOrder(): array
    {
        return ['claimDate' => 'DESC'];
    }

    public function description(): string
    {
        return 'Warranty claims (WC) — customer/field warranty requests raised against a GSE product, with their status, type, equipment model and serial number, customer, manufacturing location, sales organization, the person who entered them, the failing parts and the claim date. ';
    }

    public function fields(): array
    {
        return [
            Filter::in('statuses', 'status', enum: ['ACCEPTED', 'REJECTED', 'SALES CONCESSION', 'PENDING', 'CONDITIONAL'], desc: 'Warranty status names. Multiple = OR.'),
            Filter::in('types', 'type', desc: 'Product / equipment family the claim relates to (e.g. "Baggage Tractors", "Loaders", "Belt Loaders", "Ground Power Units"). Multiple = OR.'),
            Filter::in('equipmentModels', 'equipmentModel', desc: 'Exact equipment model names. Multiple = OR.'),
            Filter::in('serialNumbers', 'serialNumber', desc: 'Exact equipment serial numbers. Multiple = OR.'),
            Filter::like('customerNameLike', 'customerName', desc: 'Partial customer name (LIKE %value%).'),
            Filter::in('manufacturingLocationNames', 'manufacturingLocation.name', desc: 'Exact manufacturing location names. Multiple = OR.'),
            Filter::in('salesOrganizationNames', 'salesOrganization.name', desc: 'Exact sales organization location names. Multiple = OR.'),
            Filter::in('enteredByUsernames', 'enteredBy.username', desc: 'Usernames of the people who entered the claim. Multiple = OR.'),
            Filter::in('partNumbers', 'parts.partNumber', desc: 'Exact part numbers attached to the claim. Multiple = OR.'),
            Filter::inAny('failureCodes', ['failureCode1', 'failureCode2'], desc: 'Failure codes (matches either failure code 1 or 2). Multiple = OR.'),
            Filter::dateRange(
                'claimDate',
                afterName: 'claimDateAfter',
                beforeName: 'claimDateBefore',
                afterDesc: 'ISO-8601 date — claims with a claim date on/after this date.',
                beforeDesc: 'ISO-8601 date — claims with a claim date on/before this date.',
            ),
            Filter::intRange(
                'equipmentHours',
                minName: 'minEquipmentHours',
                maxName: 'maxEquipmentHours',
                minDesc: 'Minimum equipment running hours.',
                maxDesc: 'Maximum equipment running hours.',
            ),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, WarrantyClaim::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->status,
            'type' => $entity->type,
            'equipmentModel' => $entity->equipmentModel,
            'serialNumber' => $entity->serialNumber,
            'equipmentHours' => $entity->equipmentHours,
            'customerName' => $entity->customerName,
            'manufacturingLocation' => $entity->manufacturingLocation?->getName(),
            'salesOrganization' => $entity->salesOrganization?->getName(),
            'enteredBy' => $this->legacyPersonName($entity->enteredBy),
            'failureCode1' => $entity->failureCode1,
            'failureCode2' => $entity->failureCode2,
            'criticalPartFailing' => $entity->criticalPartFailing,
            'claimDate' => $entity->claimDate?->format(\DATE_ATOM),
        ];
    }
}
