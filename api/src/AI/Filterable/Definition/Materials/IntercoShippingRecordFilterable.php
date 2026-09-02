<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Materials;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use LegacyBundle\Entity\Materials\IntercoShippingRecord;
use LegacyBundle\Entity\Materials\IntercoShippingRecordLine;

final readonly class IntercoShippingRecordFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'interco_shipping_record';
    }

    public function entityClass(): string
    {
        return IntercoShippingRecord::class;
    }

    public function defaultAlias(): string
    {
        return 'isr';
    }

    public function defaultOrder(): array
    {
        return ['openedAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'Inter-company shipping records (ISR — intercompany/inter-business-unit shipments of materials and parts between Alvest business units), including status, transportation type, ERP customer number, container number and type, tracking number, origin and destination business units, the poster who created it, the shipped packing slip lines, and the opened/outbound/shipping/ETA dates.';
    }

    public function fields(): array
    {
        return [
            Filter::in('statuses', 'status', enum: ['PENDING', 'IN PROGRESS', 'CLOSED', 'CANCELLED'], desc: 'Exact ISR statuses. Open = PENDING, IN PROGRESS; closed = CLOSED; CANCELLED = cancelled. Multiple = OR.'),
            Filter::in('transportationTypes', 'transportationType', enum: ['AIR', 'OCEAN', 'ROAD', 'TRAIN'], desc: 'Transportation type. Multiple = OR.'),
            Filter::in('erpCustomerNumbers', 'erpCustomerNumber', desc: 'Exact ERP customer numbers (cuno). Multiple = OR.'),
            Filter::in('containerNumbers', 'containerNumber', desc: 'Exact container numbers. Multiple = OR.'),
            Filter::in('trackingNumbers', 'trackingNumber', desc: 'Exact tracking numbers. Multiple = OR.'),
            Filter::in('fromBusinessUnitNames', 'fromBusinessUnit.name', desc: 'Exact origin business unit (location) names the shipment departs from. Multiple = OR.'),
            Filter::in('toBusinessUnitNames', 'toBusinessUnit.name', desc: 'Exact destination business unit (location) names the shipment is sent to. Multiple = OR.'),
            Filter::concatLike('posterNameLike', ['poster.firstname', 'poster.lastname'], desc: 'Partial, case-insensitive match on the poster full name "firstname lastname" (LIKE %value%).'),
            Filter::in('packingSlipNumbers', 'lines.packingSlipNumber', desc: 'Exact packing slip numbers of the shipped lines. Multiple = OR.'),
            Filter::dateRange('openedAt', afterName: 'openedAfter', beforeName: 'openedBefore', afterDesc: 'ISO-8601 date — ISRs opened on/after this date.', beforeDesc: 'ISO-8601 date — ISRs opened on/before this date.'),
            Filter::dateRange('shippingDate', afterName: 'shippedAfter', beforeName: 'shippedBefore', afterDesc: 'ISO-8601 date — ISRs shipped on/after this date.', beforeDesc: 'ISO-8601 date — ISRs shipped on/before this date.'),
            Filter::dateRange('etaDate', afterName: 'etaAfter', beforeName: 'etaBefore', afterDesc: 'ISO-8601 date — ISRs with an ETA on/after this date.', beforeDesc: 'ISO-8601 date — ISRs with an ETA on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, IntercoShippingRecord::class);

        $poster = $entity->poster;

        return [
            'id' => $entity->getId(),
            'status' => $entity->status,
            'transportationType' => $entity->transportationType,
            'erpCustomerNumber' => $entity->erpCustomerNumber,
            'containerNumber' => $entity->containerNumber,
            'containerType' => $entity->containerType,
            'trackingNumber' => $entity->trackingNumber,
            'fromBusinessUnit' => $entity->fromBusinessUnit?->getName(),
            'toBusinessUnit' => $entity->toBusinessUnit?->getName(),
            'poster' => null === $poster ? null : \sprintf('%s %s', $poster->firstname, $poster->lastname),
            'packingSlipNumbers' => array_values(array_map(
                static fn (IntercoShippingRecordLine $line): string => $line->packingSlipNumber,
                $entity->lines->toArray(),
            )),
            'openedAt' => $entity->openedAt->format(\DATE_ATOM),
            'outboundDate' => $entity->outboundDate?->format(\DATE_ATOM),
            'shippingDate' => $entity->shippingDate?->format(\DATE_ATOM),
            'etaDate' => $entity->etaDate?->format(\DATE_ATOM),
        ];
    }
}
