<?php

declare(strict_types=1);

namespace App\AI\Factory\Materials;

use App\AI\Dto\Materials\IntercoShippingRecordLineModel;
use App\AI\Dto\Materials\IntercoShippingRecordModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use LegacyBundle\Entity\Materials\IntercoShippingRecord;
use LegacyBundle\Entity\Materials\IntercoShippingRecordLine;

final readonly class IntercoShippingRecordModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return IntercoShippingRecord::class === $class;
    }

    /**
     * @param IntercoShippingRecord $entity
     */
    public function create(object $entity): IntercoShippingRecordModel
    {
        return new IntercoShippingRecordModel(
            openedAt: $entity->openedAt,
            status: $entity->status,
            erpCustomerNumber: $entity->erpCustomerNumber,
            transportationType: $entity->transportationType,
            containerNumber: $entity->containerNumber,
            containerType: $entity->containerType,
            trackingNumber: $entity->trackingNumber,
            outboundDate: $entity->outboundDate,
            shippingDate: $entity->shippingDate,
            etaDate: $entity->etaDate,
            notes: $entity->notes,
            poster: null === $entity->poster ? null : $this->peopleModelFactory->create($entity->poster),
            fromBusinessUnit: null === $entity->fromBusinessUnit ? null : $this->locationModelFactory->create($entity->fromBusinessUnit),
            toBusinessUnit: null === $entity->toBusinessUnit ? null : $this->locationModelFactory->create($entity->toBusinessUnit),
            lines: array_values(array_map(
                static fn (IntercoShippingRecordLine $line) => new IntercoShippingRecordLineModel(
                    packingSlipNumber: $line->packingSlipNumber,
                ),
                $entity->lines->toArray(),
            )),
        );
    }
}
