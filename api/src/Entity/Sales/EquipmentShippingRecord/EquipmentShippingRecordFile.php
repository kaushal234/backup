<?php

declare(strict_types=1);

namespace App\Entity\Sales\EquipmentShippingRecord;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'equipment_shipping_record_files')]
class EquipmentShippingRecordFile extends File
{
    #[ORM\ManyToOne(targetEntity: EquipmentShippingRecord::class, inversedBy: 'files')]
    private ?EquipmentShippingRecord $equipmentShippingRecord = null;

    public function getEquipmentShippingRecord(): ?EquipmentShippingRecord
    {
        return $this->equipmentShippingRecord;
    }

    public function setEquipmentShippingRecord(?EquipmentShippingRecord $equipmentShippingRecord): self
    {
        $this->equipmentShippingRecord = $equipmentShippingRecord;

        return $this;
    }
}
