<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(operations: [])]

class AnalyticalDimensionApprover extends AbstractApprover
{
    #[ORM\Column(type: 'string')]
    #[Assert\Length(max: 6)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    public string $analyticalDimension;
}
