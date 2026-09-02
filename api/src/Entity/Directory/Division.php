<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['division', 'people_public', 'expose_legacy']]),
        new Post(security: "is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"),
        new Delete(security: "is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"),
    ],
    normalizationContext: ['groups' => ['division:detail', 'subdivision:light', 'people_public', 'expose_legacy']],
    denormalizationContext: ['groups' => ['division:write']],
)]
#[ORM\Table(name: 'directory_division')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => ['division:tree']])]
#[Loggable]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id',
    'name' => 'partial',
])]
#[Legacy\Synchronize(table: 'tld_divisions')]
class Division implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    public const INTEGRATED_THIRD_PARTIES = 'INTEGRATED THIRD PARTIES';

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['division', 'division:detail', 'division:write', 'group_member', 'map_premise_people'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Legacy\Column(column: 'name')]
    public string $name;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['division', 'division:detail'])]
    private int $id;

    /**
     * @var Collection<SubDivision>
     */
    #[ORM\OneToMany(mappedBy: 'division', targetEntity: 'App\Entity\Directory\SubDivision')]
    #[Groups(['division:detail', 'division:tree'])]
    private Collection $subDivisions;

    /**
     * @var Collection<int, People>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinTable(name: 'directory_division_representative')]
    #[Groups(['division:detail', 'division:write'])]
    private Collection $representatives;

    public function __construct()
    {
        $this->subDivisions = new ArrayCollection();
        $this->representatives = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSubDivisions(): Collection
    {
        return $this->subDivisions;
    }

    public function addSubDivision(SubDivision $subDivision): self
    {
        if (!$this->subDivisions->contains($subDivision)) {
            $subDivision->division = $this;
            $this->subDivisions->add($subDivision);
        }

        return $this;
    }

    public function removeSubDivision(SubDivision $subDivision): self
    {
        if ($this->subDivisions->contains($subDivision)) {
            $this->subDivisions->removeElement($subDivision);
        }

        return $this;
    }

    /**
     * @return Collection<int, People>
     */
    public function getRepresentatives(): Collection
    {
        return $this->representatives;
    }

    public function addRepresentative(People $representative): self
    {
        if (!$this->representatives->contains($representative)) {
            $this->representatives->add($representative);
        }

        return $this;
    }

    public function removeRepresentative(People $representative): self
    {
        if ($this->representatives->contains($representative)) {
            $this->representatives->removeElement($representative);
        }

        return $this;
    }
}
