<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Directory\PremiseTransferController;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Entity\AddressWithCountry;
use App\Entity\Common\Airport;
use App\Entity\MIS\SupportTeam;
use App\Filter\ColumnsFilter;
use App\Filter\Directory\DirectoryEntityFilter;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\PremiseArchived;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'], message: 'This value is already used by another Premise.')]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'csv', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => ['premise', 'tag', 'address', 'support_team']]
        ),
        new Post(security: "is_granted('FEATURE_PREMISE_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_PREMISE_WRITE')"),
        new Put(
            uriTemplate: '/premises/{id}/transfer',
            controller: PremiseTransferController::class,
            security: "is_granted('FEATURE_PREMISE_WRITE')",
            deserialize: false,
            name: 'transfer_premise'
        ),
    ],
    normalizationContext: ['groups' => ['premise:detail', 'tag', 'address', 'airport_list', 'support_team']],
    denormalizationContext: ['groups' => ['premise:write', 'address_write']],
)]
#[ORM\Table(name: 'premises')]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'name' => 'partial',
    'description' => 'partial',
    'address.country',
])]
#[ApiFilter(SearchFilter::class, properties: [
    'tags',
    'name' => 'partial',
    'description' => 'partial',
    'archived',
])]
#[ApiFilter(BooleanFilter::class, properties: ['archived'])]
#[ApiFilter(OrderFilter::class, properties: ['name', 'description'])]
#[ApiFilter(DirectoryEntityFilter::class, properties: ['employees'])]
#[ApiFilter(ColumnsFilter::class)]
#[Loggable]
#[PremiseArchived(errorPath: 'archived')]
class Premise
{
    #[ORM\Column(type: 'string', length: 10)]
    #[Assert\Length(max: 10)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['premise', 'premise:detail', 'premise:write', 'people:export'])]
    public string $name;

    #[ORM\Column(type: 'string')]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    #[Groups(['premise', 'premise:detail', 'premise:write'])]
    public string $description;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['premise:detail', 'premise:write'])]
    public bool $archived = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['premise:detail'])]
    public ?\DateTime $archivedAt = null;

    #[ORM\Embedded(class: 'App\Entity\AddressWithCountry')]
    #[Assert\Valid]
    #[Assert\NotNull]
    #[Groups(['premise', 'premise:detail', 'premise:write'])]
    public AddressWithCountry $address;

    #[ORM\ManyToOne(targetEntity: Airport::class)]
    #[Groups(['premise:detail', 'premise:write'])]
    public ?Airport $airport = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[ApiProperty(iris: ['https://schema.org/Float'])]
    #[Assert\NotNull]
    #[Assert\Range(notInRangeMessage: 'Latitude must be between -90 and 90.', min: -90, max: 90)]
    #[Groups(['premise', 'premise:detail', 'premise:write'])]
    public ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[ApiProperty(iris: ['https://schema.org/Float'])]
    #[Assert\NotNull]
    #[Assert\Range(notInRangeMessage: 'Longitude must be between -180 and 180.', min: -180, max: 180)]
    #[Groups(['premise', 'premise:detail', 'premise:write'])]
    public ?float $longitude = null;

    #[ORM\ManyToOne(targetEntity: SupportTeam::class, inversedBy: 'premises')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['premise', 'premise:detail', 'premise:write', 'people:export'])]
    public ?SupportTeam $supportTeam = null;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['premise:detail', 'premise'])]
    private int $id;

    /**
     * @var Collection<PremiseTag>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\PremiseTag', mappedBy: 'premises')]
    #[Assert\Count(min: 1)]
    #[Groups(['premise', 'premise:detail', 'premise:write'])]
    private Collection $tags;

    /**
     * @var Collection<People>
     */
    #[ORM\OneToMany(mappedBy: 'premise', targetEntity: 'App\Entity\Directory\People')]
    private Collection $employees;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->employees = new ArrayCollection();
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
     * @return Collection<PremiseTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(PremiseTag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $tag->addPremise($this);
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(PremiseTag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $tag->removePremise($this);
            $this->tags->removeElement($tag);
        }

        return $this;
    }

    /**
     * @return Collection<People>
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }
}
