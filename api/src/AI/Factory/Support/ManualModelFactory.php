<?php

declare(strict_types=1);

namespace App\AI\Factory\Support;

use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Support\EquipmentSerialModel;
use App\AI\Dto\Support\ManualModel;
use App\AI\Factory\ModelFactoryInterface;
use App\Entity\Support\EquipmentSerial;
use App\Entity\Support\Manual;

final class ManualModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private EquipmentRecordModelFactory $equipmentRecordModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return Manual::class === $class;
    }

    /**
     * @param Manual $entity
     */
    public function create(object $entity): ManualModel
    {
        $createdBy = $entity->createdBy;
        $equipmentSerial = $entity->equipmentSerial;
        $equipmentRecord = $entity->equipmentRecord;

        return new ManualModel(
            description: $entity->description,
            features: $entity->features,
            language: $entity->language,
            status: $entity->status,
            createdAt: $entity->createdAt,
            createdBy: null === $createdBy ? null : new PeopleModel(
                username: $createdBy->getUsername(),
                email: $createdBy->getEmail(),
                firstname: $createdBy->getFirstname(),
                lastname: $createdBy->getLastname(),
            ),
            equipmentSerial: null === $equipmentSerial ? null : $this->createEquipmentSerial($equipmentSerial),
            equipmentRecord: null === $equipmentRecord ? null : $this->equipmentRecordModelFactory->create($equipmentRecord),
        );
    }

    private function createEquipmentSerial(EquipmentSerial $serial): EquipmentSerialModel
    {
        return new EquipmentSerialModel(
            model: $serial->model,
            serial: $serial->serial,
            brand: $serial->brand,
        );
    }
}
