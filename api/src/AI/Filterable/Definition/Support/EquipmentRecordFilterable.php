<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\Support;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\Entity\EquipmentRecord;
use App\Entity\EquipmentRecordState;
use App\Entity\EquipmentRecordStatus;

final readonly class EquipmentRecordFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'equipment_record';
    }

    public function entityClass(): string
    {
        return EquipmentRecord::class;
    }

    public function defaultOrder(): array
    {
        return ['id' => 'DESC'];
    }

    public function description(): string
    {
        return 'GSE equipment records — a piece of ground support equipment identified by its serial number. Also called equipment, machine, unit or GSE. Carries product/model/type, buyer & end user, manufacturing & sales locations, airport, tag dates and lifecycle status.';
    }

    public function fields(): array
    {
        return [
            Filter::in('serialNumbers', 'serialNumber', desc: 'Exact equipment serial numbers. Multiple = OR.'),
            Filter::in('customerSerialNumbers', 'customerSerialNumber', desc: 'Exact customer serial numbers. Multiple = OR.'),
            Filter::in('models', 'model', desc: 'Exact equipment models. Multiple = OR.'),
            Filter::in('types', 'type', desc: 'Exact equipment types. Multiple = OR.'),
            Filter::in('statuses', 'status', enum: EquipmentRecordStatus::values(), desc: 'Lifecycle statuses. Multiple = OR.'),
            Filter::in('states', 'state', enum: EquipmentRecordState::name(), desc: 'Equipment states (ACTIVE or RETIRED). Multiple = OR.'),
            Filter::in('combinationModes', 'combinationMode', enum: [EquipmentRecord::COMBINATION_ER_COMBINED, EquipmentRecord::COMBINATION_PRE_ASSEMBLY], desc: 'Combination modes. Multiple = OR.'),
            Filter::in('buyerNames', 'buyer.name', desc: 'Exact buyer (customer) names. Multiple = OR.'),
            Filter::in('endUserNames', 'endUser.name', desc: 'Exact end user (customer) names. Multiple = OR.'),
            Filter::in('maintainerNames', 'maintainer.name', desc: 'Exact maintainer (customer) names. Multiple = OR.'),
            Filter::in('productNames', 'product.name', desc: 'Exact product names. Multiple = OR.'),
            Filter::in('productFamilyNames', 'product.family.name', desc: 'Exact product family names. Multiple = OR.'),
            Filter::in('productTypes', 'product.family.productType.englishName', desc: 'Exact product types (English name). Multiple = OR.'),
            Filter::in('airportCodes', 'airport.code', desc: 'Exact airport IATA/ICAO codes. Multiple = OR.'),
            Filter::in('manufacturerLocationNames', 'manufacturerLocation.name', desc: 'Exact manufacturer location names. Multiple = OR.'),
            Filter::in('salesOrganisationNames', 'salesOrganisation.name', desc: 'Exact sales organisation location names. Multiple = OR.'),
            Filter::in('projectNumbers', 'projectNumber', desc: 'Exact manufacturing project numbers. Multiple = OR.'),
            Filter::like('locationLike', 'location', desc: 'Partial physical location (LIKE %value%).'),
            Filter::bool('publishable', 'publishable', desc: 'Whether the equipment record is publishable.'),
            Filter::bool('light', 'light', desc: 'Whether the equipment is flagged as light.'),
            Filter::intRange('hourMeter', minName: 'minHourMeter', maxName: 'maxHourMeter', minDesc: 'Minimum hour meter reading.', maxDesc: 'Maximum hour meter reading.'),
            Filter::dateRange('greenTagDate', afterName: 'greenTagAfter', beforeName: 'greenTagBefore', afterDesc: 'ISO-8601 date — equipment green-tagged on/after this date.', beforeDesc: 'ISO-8601 date — equipment green-tagged on/before this date.'),
            Filter::dateRange('yellowTagDate', afterName: 'yellowTagAfter', beforeName: 'yellowTagBefore', afterDesc: 'ISO-8601 date — equipment yellow-tagged on/after this date.', beforeDesc: 'ISO-8601 date — equipment yellow-tagged on/before this date.'),
            Filter::dateRange('dateShipped', afterName: 'shippedAfter', beforeName: 'shippedBefore', afterDesc: 'ISO-8601 date — equipment shipped on/after this date.', beforeDesc: 'ISO-8601 date — equipment shipped on/before this date.'),
            Filter::dateRange('dateCommissioned', afterName: 'commissionedAfter', beforeName: 'commissionedBefore', afterDesc: 'ISO-8601 date — equipment commissioned on/after this date.', beforeDesc: 'ISO-8601 date — equipment commissioned on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, EquipmentRecord::class);

        $product = $entity->getProduct();

        return [
            'id' => $entity->getId(),
            'serialNumber' => $entity->getSerialNumber(),
            'customerSerialNumber' => $entity->getCustomerSerialNumber(),
            'model' => $entity->getModel(),
            'type' => $entity->getType(),
            'status' => $entity->getStatus(),
            'state' => $entity->getState(),
            'product' => $product?->getName(),
            'productFamily' => $product?->getFamily()->getName(),
            'buyer' => $entity->getBuyer()?->getName(),
            'endUser' => $entity->getEndUser()?->getName(),
            'airport' => $entity->getAirport()?->getCode(),
            'manufacturerLocation' => $entity->getManufacturerLocation()?->getName(),
            'greenTagDate' => $entity->getGreenTagDate()?->format(\DATE_ATOM),
            'dateShipped' => $entity->getDateShipped()?->format(\DATE_ATOM),
        ];
    }
}
