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
class DerogationFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\Derogation', inversedBy: 'files')]
    private ?Derogation $derogation = null;

    public function getDerogation(): ?Derogation
    {
        return $this->derogation;
    }

    public function setDerogation(?Derogation $derogation): self
    {
        $this->derogation = $derogation;

        return $this;
    }
}
