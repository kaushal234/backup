<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Validator\Constraints\LockedValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(fields: ['name'])]
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('POSITION_CATEGORY_WRITE_VOTER')"),
        new Get(),
        new Put(security: "is_granted('POSITION_CATEGORY_WRITE_VOTER')"),
        new Delete(security: "is_granted('POSITION_CATEGORY_WRITE_VOTER')"),
    ],
    normalizationContext: ['groups' => ['position_category', 'position_category_type', 'division']],
    denormalizationContext: ['groups' => ['position_category:write']],
)]
#[ORM\Table(name: 'directory_position_category')]
#[ORM\UniqueConstraint(name: 'unique_position_category_name', columns: ['name'])]
#[ApiFilter(OrderFilter::class, properties: ['name', 'directHeadcount', 'positionCategoryType.name'])]
#[ApiFilter(SearchFilter::class, properties: ['divisions.subDivisions.regions.businessUnits'])]
#[App\Loggable]
#[LockedValue(value: PositionCategory::FINANCE_ACCOUNTING, propertyPath: 'name')]
#[LockedValue(value: PositionCategory::GENERAL_MANAGEMENT, propertyPath: 'name')]
class PositionCategory
{
    public const FINANCE_ACCOUNTING = 'Finance & Accounting';

    public const GENERAL_MANAGEMENT = 'General Management';

    #[ORM\Column(type: 'string')]
    #[Groups(['position_category', 'position_category:write'])]
    #[Assert\NotBlank]
    public string $name;

    #[ORM\Column(type: 'string', length: 500)]
    #[Assert\Length(max: 500)]
    #[Assert\NotNull]
    #[Groups(['position_category', 'position_category:write'])]
    public string $description;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\PositionCategoryType')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['position_category', 'position_category:write'])]
    #[Assert\NotNull]
    public PositionCategoryType $positionCategoryType;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['position_category', 'position_category:write'])]
    #[Assert\NotNull]
    public bool $directHeadcount = false;

    /**
     * @var Collection<Division>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\Division')]
    #[Groups(['position_category', 'position_category:write'])]
    private Collection $divisions;

    #[ORM\OneToMany(mappedBy: 'positionCategory', targetEntity: 'App\Entity\Directory\PositionClassification', cascade: ['remove', 'persist'], orphanRemoval: true)]
    private Collection $positionClassifications;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['position_category'])]
    private int $id;

    public function __construct()
    {
        $this->divisions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDivisions(): Collection
    {
        return $this->divisions;
    }

    public function addDivision(Division $division): self
    {
        if (!$this->divisions->contains($division)) {
            $this->divisions->add($division);
        }

        return $this;
    }

    public function removeDivision(Division $division): self
    {
        if ($this->divisions->contains($division)) {
            $this->divisions->removeElement($division);
        }

        return $this;
    }
}
