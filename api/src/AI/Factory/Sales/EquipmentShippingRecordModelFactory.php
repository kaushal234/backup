<?php

declare(strict_types=1);

namespace App\AI\Factory\Sales;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Sales\CustomerModel;
use App\AI\Dto\Sales\EquipmentShippingRecord\EquipmentShippingRecordCostModel;
use App\AI\Dto\Sales\EquipmentShippingRecord\EquipmentShippingRecordLineModel;
use App\AI\Dto\Sales\EquipmentShippingRecord\EquipmentShippingRecordModel;
use App\AI\Dto\Sales\EquipmentShippingRecord\FreightForwarderModel;
use App\AI\Dto\Sales\EquipmentShippingRecord\IncotermModel;
use App\AI\Factory\ModelFactoryInterface;
use App\AI\Factory\Support\EquipmentRecordModelFactory;
use App\Entity\FreightForwarder;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordCost;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;

final class EquipmentShippingRecordModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private EquipmentRecordModelFactory $equipmentRecordModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return EquipmentShippingRecord::class === $class;
    }

    /**
     * @param EquipmentShippingRecord $entity
     */
    public function create(object $entity): EquipmentShippingRecordModel
    {
        return new EquipmentShippingRecordModel(
            status: $entity->getStatus(),
            modality: $entity->modality,
            shipAuthorization: $entity->shipAuthorization,
            createdAt: $entity->createdAt,
            loadingPlace: $entity->loadingPlace,
            departurePlace: $entity->departurePlace,
            arrivalPlace: $entity->arrivalPlace,
            notes: $entity->notes,
            sso: new LocationModel(
                name: $entity->sso->getName(),
                erp: $entity->sso->getErp(),
            ),
            customer: null === $entity->customer ? null : new CustomerModel(
                name: $entity->customer->getName(),
                status: $entity->customer->getStatus(),
            ),
            incoterm: new IncotermModel(code: $entity->incoterm->code),
            forwarder: null === $entity->forwarder ? null : $this->createForwarder($entity->forwarder),
            carrier: null === $entity->carrier ? null : $this->createForwarder($entity->carrier),
            lines: array_values(array_map(
                fn (EquipmentShippingRecordLine $line) => new EquipmentShippingRecordLineModel(
                    equipmentRecord: $this->equipmentRecordModelFactory->create($line->equipmentRecord),
                    pickupDate: $line->pickupDate ?? null,
                    deliveryDate: $line->deliveryDate ?? null,
                ),
                $entity->getEquipmentShippingRecordLines()->toArray(),
            )),
            costs: array_values(array_map(
                static fn (EquipmentShippingRecordCost $cost) => new EquipmentShippingRecordCostModel(
                    amount: $cost->amount ?? null,
                    currency: $cost->currency->getName(),
                    description: $cost->description ?? null,
                ),
                $entity->getEquipmentShippingRecordCosts()->toArray(),
            )),
        );
    }

    private function createForwarder(FreightForwarder $forwarder): FreightForwarderModel
    {
        return new FreightForwarderModel(name: $forwarder->getName());
    }
}
