<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\Quality\Crab;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(),
    ],
    routePrefix: 'parts',
    normalizationContext: [],
    denormalizationContext: []
)]
class CrabPart extends Part
{
    #[ORM\OneToOne(mappedBy: 'part', targetEntity: 'App\Entity\Quality\Crab')]
    public Crab $crab;

    public function __construct()
    {
        $this->quantity = 1;
    }
}
