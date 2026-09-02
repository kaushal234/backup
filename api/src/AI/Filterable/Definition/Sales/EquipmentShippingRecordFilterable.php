<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Sales;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;

final readonly class EquipmentShippingRecordFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'equipment_shipping_record';
    }

    public function entityClass(): string
    {
        return EquipmentShippingRecord::class;
    }

    public function defaultAlias(): string
    {
        return 'esr';
    }

    public function defaultOrder(): array
    {
        return ['createdAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'Equipment shipping records (ESR — GSE shipments tracked by sales/logistics), including status, transport modality, SSO, customer, incoterm, loading/departure/arrival places, forwarder and carrier, ship authorization, the shipped equipment records (serial number, manufacturer location) and creation date.';
    }

    public function fields(): array
    {
        return [
            Filter::in('statuses', 'status', enum: [
                EquipmentShippingRecord::PENDING, EquipmentShippingRecord::BOOKED,
                EquipmentShippingRecord::SHIPPED, EquipmentShippingRecord::CLOSED,
            ], desc: 'Exact ESR statuses. Open = PENDING, BOOKED, SHIPPED; closed = CLOSED. Multiple = OR.'),
            Filter::in('modalities', 'modality', enum: [
                EquipmentShippingRecord::AIR, EquipmentShippingRecord::ROAD, EquipmentShippingRecord::SEA,
            ], desc: 'Transport modality. Multiple = OR.'),
            Filter::in('ssoNames', 'sso.name', desc: 'Exact SSO (sales office) location names. Multiple = OR.'),
            Filter::inInt('customerIds', 'customer', desc: 'IDs of the customers. Multiple = OR.'),
            Filter::like('customerNameLike', 'customer.name', desc: 'Partial, case-insensitive match on the customer name (LIKE %value%).'),
            Filter::in('incotermCodes', 'incoterm.code', desc: 'Exact incoterm codes (e.g. FCA, EXW, DAP). Multiple = OR.'),
            Filter::like('loadingPlaceLike', 'loadingPlace', desc: 'Partial, case-insensitive match on the loading place (LIKE %value%).'),
            Filter::like('departurePlaceLike', 'departurePlace', desc: 'Partial, case-insensitive match on the departure place (LIKE %value%).'),
            Filter::like('arrivalPlaceLike', 'arrivalPlace', desc: 'Partial, case-insensitive match on the arrival place (LIKE %value%).'),
            Filter::in('forwarderNames', 'forwarder.name', desc: 'Exact freight forwarder names. Multiple = OR.'),
            Filter::in('carrierNames', 'carrier.name', desc: 'Exact carrier names. Multiple = OR.'),
            Filter::bool('shipAuthorization', 'shipAuthorization', desc: 'True = only ESRs authorized to ship, false = only not authorized, omit for both.'),
            Filter::in('serialNumbers', 'equipmentShippingRecordLines.equipmentRecord.serialNumber', desc: 'Exact serial numbers of the shipped equipment records. Multiple = OR.'),
            Filter::in('manufacturerLocationNames', 'equipmentShippingRecordLines.equipmentRecord.manufacturerLocation.name', desc: 'Exact manufacturer location names of the shipped equipment records. Multiple = OR.'),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — ESRs created on/after this date.', beforeDesc: 'ISO-8601 date — ESRs created on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, EquipmentShippingRecord::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->getStatus(),
            'modality' => $entity->modality,
            'sso' => $entity->sso->getName(),
            'customer' => $entity->customer?->getName(),
            'incoterm' => $entity->incoterm->code,
            'loadingPlace' => $entity->loadingPlace,
            'departurePlace' => $entity->departurePlace,
            'arrivalPlace' => $entity->arrivalPlace,
            'forwarder' => $entity->forwarder?->getName(),
            'carrier' => $entity->carrier?->getName(),
            'shipAuthorization' => $entity->shipAuthorization,
            'serialNumbers' => array_values(array_map(
                static fn (EquipmentShippingRecordLine $line): string => $line->equipmentRecord->getSerialNumber(),
                $entity->getEquipmentShippingRecordLines()->toArray(),
            )),
            'createdAt' => $entity->createdAt->format(\DATE_ATOM),
        ];
    }
}
