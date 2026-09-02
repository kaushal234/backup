<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table]
class SparePartsRequestFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Parts\SparePartsRequest', inversedBy: 'sparePartsRequestFiles')]
    public ?SparePartsRequest $sparePartsRequest = null;

    public function getSparePartsRequest(): ?SparePartsRequest
    {
        return $this->sparePartsRequest;
    }

    public function setSparePartsRequest(?SparePartsRequest $sparePartsRequest): self
    {
        $this->sparePartsRequest = $sparePartsRequest;

        return $this;
    }
}
