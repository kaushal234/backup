<?php

declare(strict_types=1);

namespace App\Entity\Module\Specification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\Mapping\Attributes as App;
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
#[App\Loggable(owner: 'userStory', ownerRelation: 'roleAccesses')]
class RoleAccess extends Access
{
    #[ORM\ManyToOne(targetEntity: UserStory::class, inversedBy: 'roleAccesses')]
    public UserStory $userStory;
}
