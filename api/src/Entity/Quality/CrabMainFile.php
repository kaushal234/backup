<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table]
class CrabMainFile extends File
{
    #[Groups(['file', 'file_public_write'])]
    protected bool $public = true;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\Crab', inversedBy: 'mainFiles')]
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
