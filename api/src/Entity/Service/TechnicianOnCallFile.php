<?php

declare(strict_types=1);

namespace App\Entity\Service;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'technician_on_call_files')]
#[App\Loggable(owner: 'technicianOnCall', ownerRelation: 'files')]
class TechnicianOnCallFile extends File
{
    #[ORM\ManyToOne(targetEntity: TechnicianOnCall::class, inversedBy: 'files')]
    private ?TechnicianOnCall $technicianOnCall = null;

    public function getTechnicianOnCall(): ?TechnicianOnCall
    {
        return $this->technicianOnCall;
    }

    public function setTechnicianOnCall(?TechnicianOnCall $technicianOnCall): self
    {
        $this->technicianOnCall = $technicianOnCall;

        return $this;
    }
}
