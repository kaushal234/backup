<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Entity\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'unique_division_per_position', columns: ['position_id', 'division_id'])]
#[UniqueEntity(fields: ['position', 'division'], message: 'This value is already used.')]
#[ApiResource(
    operations: [
        new Get(),
    ],
    normalizationContext: [],
    denormalizationContext: [],
)]
#[ORM\Table]
#[Loggable(owner: 'position', ownerRelation: 'divisionGroups')]
class DivisionGroup
{
    #[ORM\ManyToOne(targetEntity: Division::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['division_group', 'division_group:write', 'group_member'])]
    public Division $division;

    #[ORM\ManyToOne(targetEntity: Position::class, inversedBy: 'divisionGroups')]
    #[ORM\JoinColumn(nullable: false)]
    public Position $position;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['division_group', 'division_group:write'])]
    private int $id;

    /**
     * @var Collection<Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class)]
    #[Groups(['division_group', 'division_group:write', 'group_member'])]
    private Collection $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    public function removeGroup(Group $group): self
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }

        return $this;
    }

    /**
     * @return Collection<Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }
}
