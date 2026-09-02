<?php

declare(strict_types=1);

namespace App\Entity\Module\Specification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'mis',
    normalizationContext: ['groups' => ['access', 'group']],
    denormalizationContext: []
)]
#[ORM\Entity]
class EmailAccess extends Access
{
    #[ORM\ManyToOne(targetEntity: Email::class, inversedBy: 'recipientAccesses')]
    public ?Email $recipientEmail = null;
    #[ORM\ManyToOne(targetEntity: Email::class, inversedBy: 'copyAccesses')]
    public ?Email $copyEmail = null;
}
