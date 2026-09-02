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
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'mis',
    normalizationContext: ['groups' => ['email', 'access', 'group']],
    denormalizationContext: ['groups' => ['access:write', 'email:write']]
)]
#[ORM\Entity]
#[Table(name: 'user_story_emails')]
#[App\Loggable(owner: 'userStory', ownerRelation: 'email')]
class Email
{
    #[ORM\Column(type: 'string')]
    #[Groups(['email', 'email:write'])]
    public string $object;

    #[ORM\Column(type: 'text')]
    #[Groups(['email', 'email:write'])]
    public string $body;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    #[Groups(['email', 'email:write'])]
    public array $peopleProperties = [];

    #[ORM\Column(type: 'boolean')]
    #[Groups(['email', 'email:write'])]
    public bool $follower = false;

    #[ORM\ManyToOne(targetEntity: UserStory::class, inversedBy: 'emails')]
    public UserStory $userStory;
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['email'])]
    private int $id;
    #[ORM\OneToMany(targetEntity: EmailAccess::class, mappedBy: 'recipientEmail', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['email', 'email:write'])]
    private Collection $recipientAccesses;
    #[ORM\OneToMany(targetEntity: EmailAccess::class, mappedBy: 'copyEmail', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['email', 'email:write'])]
    private Collection $copyAccesses;

    public function __construct()
    {
        $this->recipientAccesses = new ArrayCollection();
        $this->copyAccesses = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRecipientAccesses(): ?Collection
    {
        return $this->recipientAccesses;
    }

    public function addRecipientAccess(EmailAccess $recipientAccess): self
    {
        $this->recipientAccesses->add($recipientAccess);
        $recipientAccess->recipientEmail = $this;

        return $this;
    }

    public function removeRecipientAccess(EmailAccess $recipientAccess): self
    {
        $this->recipientAccesses->removeElement($recipientAccess);

        return $this;
    }

    public function getCopyAccesses(): ?Collection
    {
        return $this->copyAccesses;
    }

    public function addCopyAccess(EmailAccess $copyAccess): self
    {
        $this->copyAccesses->add($copyAccess);
        $copyAccess->copyEmail = $this;

        return $this;
    }

    public function removeCopyAccess(EmailAccess $copyAccess): self
    {
        $this->copyAccesses->removeElement($copyAccess);

        return $this;
    }
}
