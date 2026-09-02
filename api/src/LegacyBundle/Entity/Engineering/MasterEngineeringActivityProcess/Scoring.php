<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Scoring
{
    #[ORM\Column(name: 'ifactor', type: 'integer')]
    public int $importanceFactor = 1;

    #[ORM\Column(name: 'final_fweight', type: 'integer')]
    public int $finalWeight = 0;
}
