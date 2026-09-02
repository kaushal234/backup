<?php

declare(strict_types=1);

namespace App\Link\ResourceSourceProvider\Support;

use App\Entity\EquipmentRecord;
use App\Link\ResourceSourceProvider\ResourceSourceProviderInterface;

class EquipmentRecordResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getService(): string
    {
        return 'equipmentResourceService';
    }

    public function getProperties(): string
    {
        return 'id identifier plateNumber equipmentType { id name } equipmentModel { id name } organization { id name } energySource { id name } astusId';
    }

    public function getReturnedFields(): string
    {
        return 'id plateNumber';
    }

    public function supports(string $class): bool
    {
        return EquipmentRecord::class === $class;
    }
}
