<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'non_conformity_files')]
#[App\Loggable(owner: 'nonConformity', ownerRelation: 'files')]
class NonConformityFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\NonConformity', inversedBy: 'files')]
    private ?NonConformity $nonConformity = null;

    public function getNonConformity(): ?NonConformity
    {
        return $this->nonConformity;
    }

    public function setNonConformity(?NonConformity $nonConformity): self
    {
        $this->nonConformity = $nonConformity;

        return $this;
    }
}
