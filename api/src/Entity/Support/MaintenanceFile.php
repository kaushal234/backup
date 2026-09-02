<?php

declare(strict_types=1);

namespace App\Entity\Support;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'equipment_maintenance_files')]
#[App\Loggable(owner: 'maintenance', ownerRelation: 'maintenanceFiles')]
class MaintenanceFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Support\EquipmentMaintenance', inversedBy: 'maintenanceFiles')]
    #[ORM\JoinColumn(nullable: false)]
    private EquipmentMaintenance $maintenance;

    public function getMaintenance(): EquipmentMaintenance
    {
        return $this->maintenance;
    }

    public function setMaintenance(EquipmentMaintenance $maintenance): self
    {
        $this->maintenance = $maintenance;

        return $this;
    }
}
