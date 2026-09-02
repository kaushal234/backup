<?php

declare(strict_types=1);

namespace App\Entity\MIS;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\Premise;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'support_teams')]
#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: 'is_granted("FEATURE_CREATE_SUPPORT_TEAM")'),
        new Put(security: 'is_granted("FEATURE_EDIT_SUPPORT_TEAM")'),
        new Delete(security: 'is_granted("FEATURE_DELETE_SUPPORT_TEAM")'),
    ],
    routePrefix: 'mis',
    normalizationContext: ['groups' => ['support_team']],
    denormalizationContext: ['groups' => ['support_team:write']],
)]
#[ApiFilter(OrderFilter::class, properties: ['name', 'id'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id',
    'name' => 'partial',
])]
#[App\Loggable]
class SupportTeam
{
    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['support_team', 'support_team:write', 'people:export'])]
    public string $name;

    #[ORM\OneToMany(targetEntity: Premise::class, mappedBy: 'supportTeam')]
    private Collection $premises;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['support_team'])]
    private int $id;

    public function __construct()
    {
        $this->premises = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<Premise>
     */
    public function getPremises(): Collection
    {
        return $this->premises;
    }

    public function addPremise(Premise $premise): self
    {
        if (!$this->premises->contains($premise)) {
            $this->premises->add($premise);
        }

        return $this;
    }

    public function removePremise(Premise $premise): self
    {
        if ($this->premises->contains($premise)) {
            $this->premises->removeElement($premise);
        }

        return $this;
    }
}
