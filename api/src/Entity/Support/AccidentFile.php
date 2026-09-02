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
#[ORM\Table(name: 'equipment_accidents_files')]
#[App\Loggable(owner: 'accident', ownerRelation: 'accidentFiles')]
class AccidentFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Support\EquipmentAccident', inversedBy: 'accidentFiles')]
    #[ORM\JoinColumn(nullable: false)]
    private EquipmentAccident $accident;

    public function getAccident(): EquipmentAccident
    {
        return $this->accident;
    }

    /**
     * @return $this
     */
    public function setAccident(EquipmentAccident $accident): self
    {
        $this->accident = $accident;

        return $this;
    }
}
