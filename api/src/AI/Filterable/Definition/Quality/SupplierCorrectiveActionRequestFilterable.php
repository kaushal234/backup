<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Quality;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\Entity\Parts\SupplierCorrectiveActionRequestPart;
use App\Entity\Quality\SupplierCorrectiveActionRequest;

final readonly class SupplierCorrectiveActionRequestFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'supplier_corrective_action_request';
    }

    public function entityClass(): string
    {
        return SupplierCorrectiveActionRequest::class;
    }

    public function defaultOrder(): array
    {
        return ['createdAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'Supplier Corrective Action Requests (SCAR), also known as supplier corrective actions, 8D requests, or corrective action requests to a supplier. ';
    }

    public function fields(): array
    {
        $statuses = [
            SupplierCorrectiveActionRequest::PENDING,
            SupplierCorrectiveActionRequest::VENDOR_TO_FILL_FORM,
            SupplierCorrectiveActionRequest::TLD_TO_REVIEW_FORM,
            SupplierCorrectiveActionRequest::VALIDATION,
            SupplierCorrectiveActionRequest::COMMERCIAL_AGREEMENT,
            SupplierCorrectiveActionRequest::CLOSED,
            SupplierCorrectiveActionRequest::CANCEL,
        ];
        $importanceFactors = [
            SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_1,
            SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_10,
            SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_100,
            SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_1000,
        ];

        return [
            Filter::in('statuses', 'status', enum: $statuses, desc: 'SCAR statuses. Multiple = OR.'),
            Filter::in('importanceFactors', 'iFactor', enum: $importanceFactors, desc: 'Importance factors (severity). Multiple = OR.'),
            Filter::in('supplierNumbers', 'supplierNumber', desc: 'Exact supplier numbers (business partner codes).'),
            Filter::like('supplierNameLike', 'supplierName', desc: 'Partial supplier name (LIKE %value%).'),
            Filter::like('shortDescriptionLike', 'shortDescription', desc: 'Partial short description / title (LIKE %value%).'),
            Filter::in('partNumbers', 'parts.partNumber', desc: 'Exact part numbers attached to the SCAR.'),
            Filter::in('factoryNames', 'factory.name', desc: 'Exact factory location names.'),
            Filter::inInt('posterUserIds', 'poster', desc: 'IDs of creators (Users).'),
            Filter::inInt('leaderPeopleIds', 'leader', desc: 'IDs of leaders (People).'),
            Filter::inInt('representativePeopleIds', 'representative', desc: 'IDs of representatives (People).'),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — SCARs created on/after this date.', beforeDesc: 'ISO-8601 date — SCARs created on/before this date.'),
            Filter::dateRange('closedAt', afterName: 'closedAfter', beforeName: 'closedBefore', afterDesc: 'ISO-8601 date — SCARs closed on/after this date.', beforeDesc: 'ISO-8601 date — SCARs closed on/before this date.'),
            Filter::exists('isClosed', 'closedAt', desc: 'True = only closed, false = only open, omit for both.'),
            Filter::hasMany('hasVendorWarrantyClaim', 'vendorWarrantyClaims', desc: 'True = only SCARs linked to a vendor warranty claim (VWC), false = without, omit for both.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, SupplierCorrectiveActionRequest::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->getStatus(),
            'importanceFactor' => $entity->iFactor,
            'shortDescription' => $entity->shortDescription,
            'supplierNumber' => $entity->getSupplierNumber(),
            'supplierName' => $entity->getSupplierName(),
            'factory' => $entity->factory->getName(),
            'leader' => $this->personName($entity->leader),
            'representative' => $this->personName($entity->representative),
            'partNumbers' => array_values(array_map(
                static fn (SupplierCorrectiveActionRequestPart $part): string => $part->partNumber,
                $entity->getParts()->toArray(),
            )),
            'vendorWarrantyClaimCount' => $entity->getVendorWarrantyClaims()->count(),
            'createdAt' => $entity->createdAt->format(\DATE_ATOM),
            'closedAt' => $entity->closedAt?->format(\DATE_ATOM),
            'approvedAt' => $entity->approvedAt?->format(\DATE_ATOM),
        ];
    }
}
