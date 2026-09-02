<?php

declare(strict_types=1);

namespace App\Entity\Module\Specification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\Mapping\Attributes as App;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    shortName: 'user_story_notification',
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'mis',
    normalizationContext: ['groups' => ['notification', 'access', 'group']],
)]
#[ORM\Entity]
#[Table(name: 'user_story_notifications')]
#[App\Loggable(owner: 'userStory', ownerRelation: 'notification')]
class Notification
{
    #[ORM\Column(type: 'string')]
    #[Groups(['notification', 'notification:write'])]
    public string $message;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['notification', 'notification:write'])]
    public bool $follower = false;

    #[ORM\ManyToOne(targetEntity: UserStory::class, inversedBy: 'notifications')]
    public UserStory $userStory;
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['notification', 'notification:write'])]
    private int $id;
    #[ORM\OneToMany(targetEntity: NotificationAccess::class, mappedBy: 'roleToNotify', cascade: ['persist', 'remove'],
        orphanRemoval: true)]
    #[Groups(['notification', 'notification:write'])]
    private Collection $roleToNotify;

    public function __construct()
    {
        $this->roleToNotify = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRoleToNotify(): ?Collection
    {
        return $this->roleToNotify;
    }

    public function addRoleToNotify(NotificationAccess $roleToNotify): self
    {
        $this->roleToNotify->add($roleToNotify);
        $roleToNotify->roleToNotify = $this;

        return $this;
    }

    public function removeRoleToNotify(NotificationAccess $roleToNotify): self
    {
        $this->roleToNotify->removeElement($roleToNotify);

        return $this;
    }
}
