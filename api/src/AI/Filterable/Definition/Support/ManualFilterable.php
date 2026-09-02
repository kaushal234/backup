<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Support;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\Entity\Support\Manual;

final readonly class ManualFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'manual';
    }

    public function entityClass(): string
    {
        return Manual::class;
    }

    public function defaultOrder(): array
    {
        return ['id' => 'DESC'];
    }

    public function description(): string
    {
        return 'Technical equipment manuals (documentation, spare parts catalogue) attached to an equipment record / GSE serial number. Also called manual, documentation or parts manual.';
    }

    public function fields(): array
    {
        return [
            Filter::like('descriptionLike', 'description', desc: 'Partial manual description (LIKE %value%).'),
            Filter::in('statuses', 'status', enum: Manual::STATUS, desc: 'Manual statuses. Multiple = OR.'),
            Filter::eq('language', 'language', desc: 'Language code of the manual (e.g. "en", "fr").'),
            Filter::in('serialNumbers', 'equipmentRecord.serialNumber', desc: 'Exact equipment record serial numbers. Multiple = OR.'),
            Filter::in('productFamilyNames', 'equipmentRecord.product.family.name', desc: 'Exact product family names of the equipment. Multiple = OR.'),
            Filter::in('partNumbers', 'documents.parts.partNumber', desc: 'Exact part numbers referenced in the manual documents. Multiple = OR.'),
            Filter::inInt('createdByPeopleIds', 'createdBy', desc: 'IDs of the People who created the manual.'),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — manuals created on/after this date.', beforeDesc: 'ISO-8601 date — manuals created on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, Manual::class);

        $equipmentRecord = $entity->equipmentRecord;

        return [
            'id' => $entity->getId(),
            'status' => $entity->status,
            'language' => $entity->language,
            'description' => $entity->description,
            'serialNumber' => $equipmentRecord?->getSerialNumber(),
            'model' => $equipmentRecord?->getModel(),
            'product' => $equipmentRecord?->getProduct()?->getName(),
            'createdBy' => $this->personName($entity->createdBy),
            'createdAt' => $entity->createdAt?->format(\DATE_ATOM),
        ];
    }
}
