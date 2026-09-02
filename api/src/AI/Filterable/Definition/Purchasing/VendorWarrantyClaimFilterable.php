<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Purchasing;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\FilterableField;
use App\Entity\Purchasing\NCRVendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Entity\Purchasing\WCVendorWarrantyClaim;
use Doctrine\ORM\QueryBuilder;

final readonly class VendorWarrantyClaimFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'vendor_warranty_claim';
    }

    public function entityClass(): string
    {
        return VendorWarrantyClaim::class;
    }

    public function defaultOrder(): array
    {
        return ['createdAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'Vendor warranty claims (VWC), also known as supplier claims, warranty requests, supplier returns. ';
    }

    public function fields(): array
    {
        $statuses = [
            VendorWarrantyClaimStatus::PENDING,
            VendorWarrantyClaimStatus::QA_ANALYSIS,
            VendorWarrantyClaimStatus::VENDOR_TO_RESPOND,
            VendorWarrantyClaimStatus::REVIEW_VENDOR_RESPONSE,
            VendorWarrantyClaimStatus::CREATE_PO,
            VendorWarrantyClaimStatus::SHIP_TO_VENDOR,
            VendorWarrantyClaimStatus::ISSUE_CREDIT_NOTE,
            VendorWarrantyClaimStatus::REC_FROM_VENDOR,
            VendorWarrantyClaimStatus::ISSUE_DEBIT_NOTE,
            VendorWarrantyClaimStatus::VALIDATE_SCAR,
            VendorWarrantyClaimStatus::CLOSED_RESOLVED,
            VendorWarrantyClaimStatus::CLOSED_LOW_VALUE,
            VendorWarrantyClaimStatus::CLOSED_VENDOR_REJECTED,
            VendorWarrantyClaimStatus::CLOSED_DUPLICATE,
            VendorWarrantyClaimStatus::CLOSED_NOT_VENDOR_ISSUE,
        ];

        return [
            Filter::in('statuses', 'status.name', enum: $statuses, desc: 'Status names. Multiple = OR.'),
            Filter::in('supplierNumbers', 'supplierNumber', desc: 'Exact supplier numbers (business partner codes).'),
            Filter::like('supplierNameLike', 'supplierName', desc: 'Partial supplier name (LIKE %value%).'),
            Filter::in('partNumbers', 'parts.partNumber', desc: 'Exact part numbers attached to the claim.'),
            Filter::inInt('assigneePeopleIds', 'assignee', desc: 'IDs of assignees (People).'),
            Filter::inInt('posterPeopleIds', 'poster', desc: 'IDs of creators/posters (People).'),
            Filter::in('locationNames', 'location.name', desc: 'Exact location names.'),
            Filter::in('factoryNames', 'factory.name', desc: 'Exact factory location names.'),
            Filter::in('typeNames', 'type.name', desc: 'Exact VWC type names.'),
            Filter::custom(
                fields: [new FilterableField('discriminator', FilterableField::TYPE_STRING, enum: ['ncr', 'wc'], description: 'Restrict to one VWC subtype.')],
                apply: static function (QueryBuilder $qb, PathResolver $paths, array $filters): void {
                    $value = $filters['discriminator'] ?? null;
                    if (null === $value || '' === $value) {
                        return;
                    }
                    $class = match (mb_strtolower((string) $value)) {
                        'ncr' => NCRVendorWarrantyClaim::class,
                        'wc' => WCVendorWarrantyClaim::class,
                        default => null,
                    };
                    if (null !== $class) {
                        $qb->andWhere(\sprintf('%s INSTANCE OF %s', $qb->getRootAliases()[0], $class));
                    }
                },
            ),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — claims created on/after this date.', beforeDesc: 'ISO-8601 date — claims created on/before this date.'),
            Filter::dateRange('closedAt', afterName: 'closedAfter', beforeName: 'closedBefore', afterDesc: 'ISO-8601 date — claims closed on/after this date.', beforeDesc: 'ISO-8601 date — claims closed on/before this date.'),
            Filter::exists('isClosed', 'closedAt', desc: 'True = only closed, false = only open, omit for both.'),
            Filter::exists('hasAssignee', 'assignee', desc: 'True = with assignee, false = without, omit for both.'),
            Filter::bool('scarRequested', 'scarRequested', desc: 'Whether a SCAR was requested.'),
            Filter::bool('accepted', 'accepted', desc: 'Whether the claim was accepted by the supplier.'),
            Filter::bool('shipBackDefectivePart', 'shipBackDefectivePart', desc: 'Whether the defective part must be shipped back.'),
            Filter::floatRange('requestedCreditAmount', minName: 'minRequestedCreditAmount', maxName: 'maxRequestedCreditAmount', minDesc: 'Minimum requested credit amount.', maxDesc: 'Maximum requested credit amount.'),
            Filter::floatRange('supplierCreditAmount', minName: 'minSupplierCreditAmount', maxName: 'maxSupplierCreditAmount', minDesc: 'Minimum supplier credit amount.', maxDesc: 'Maximum supplier credit amount.'),
            Filter::floatRange('actualCreditAmount', minName: 'minActualCreditAmount', maxName: 'maxActualCreditAmount', minDesc: 'Minimum actual credit amount.', maxDesc: 'Maximum actual credit amount.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, VendorWarrantyClaim::class);

        return [
            'id' => $entity->getId(),
            'kind' => $entity instanceof NCRVendorWarrantyClaim ? 'ncr' : 'wc',
            'status' => $entity->status->name,
            'type' => $entity->type?->name,
            'supplierNumber' => $entity->getSupplierNumber(),
            'supplierName' => $entity->getSupplierName(),
            'location' => $entity->location->getName(),
            'factory' => $entity->factory?->getName(),
            'assignee' => $this->personName($entity->assignee),
            'scarRequested' => $entity->scarRequested,
            'accepted' => $entity->accepted,
            'requestedCreditAmount' => $entity->requestedCreditAmount,
            'supplierCreditAmount' => $entity->supplierCreditAmount,
            'actualCreditAmount' => $entity->actualCreditAmount,
            'currency' => $entity->currency?->getName(),
            'createdAt' => $entity->createdAt->format(\DATE_ATOM),
            'closedAt' => $entity->closedAt?->format(\DATE_ATOM),
        ];
    }
}
