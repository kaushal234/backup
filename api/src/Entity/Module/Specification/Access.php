<?php

declare(strict_types=1);

namespace App\Entity\Module\Specification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

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
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap([
    'role_access' => 'App\Entity\Module\Specification\RoleAccess',
    'email_access' => 'App\Entity\Module\Specification\EmailAccess',
    'notification_access' => 'App\Entity\Module\Specification\NotificationAccess',
])]
abstract class Access
{
    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[Groups(['access', 'access:write'])]
    public Group $group;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['access', 'access:write'])]
    public ?string $locationProperty = null;
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['access'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
