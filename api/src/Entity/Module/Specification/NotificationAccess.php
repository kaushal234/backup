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
class NotificationAccess extends Access
{
    #[ORM\ManyToOne(targetEntity: Notification::class, inversedBy: 'roleToNotify')]
    public ?Notification $roleToNotify = null;
}
