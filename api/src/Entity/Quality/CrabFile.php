<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table]
class CrabFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\Crab', inversedBy: 'files')]
    private ?Crab $crab = null;

    public function getCrab(): ?Crab
    {
        return $this->crab;
    }

    public function setCrab(?Crab $crab): self
    {
        $this->crab = $crab;

        return $this;
    }
}
