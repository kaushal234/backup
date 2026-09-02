<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Repository\Directory\PositionClassificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[UniqueEntity(fields: ['businessUnit', 'positionCategory'], message: 'This position category is already defined for this business unit.', errorPath: 'positionCategory')]
#[ORM\Entity(repositoryClass: PositionClassificationRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(securityPostDenormalize: "is_granted('POSITION_CLASSIFICATION_WRITE_VOTER', object.businessUnit)"),
        new Get(),
        new Put(security: "is_granted('POSITION_CLASSIFICATION_WRITE_VOTER', object.businessUnit)"),
        new Delete(security: "is_granted('POSITION_CLASSIFICATION_WRITE_VOTER', object.businessUnit)"),
    ],
    normalizationContext: ['groups' => ['position_classification', 'position_category', 'business_unit_public', 'position', 'position_category_type']],
    denormalizationContext: ['groups' => ['position_classification:write']],
)]
#[ORM\Table(name: 'directory_position_classification')]
#[ORM\UniqueConstraint(name: 'unique_category_per_business_unit', columns: ['business_unit_id', 'position_category_id'])]
#[ApiFilter(SearchFilter::class, properties: ['businessUnit', 'positionCategory.divisions.subDivisions.regions.businessUnits', 'businessUnit.legacyId', 'positions.description', 'positionCategory.name'])]
#[App\Loggable]
class PositionClassification
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\BusinessUnit', inversedBy: 'positionClassifications')]
    #[Groups(['position_classification', 'position_classification:write'])]
    public BusinessUnit $businessUnit;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\PositionCategory', inversedBy: 'positionClassifications')]
    #[Groups(['position_classification', 'position_classification:write'])]
    public PositionCategory $positionCategory;

    #[ORM\Column(type: 'float')]
    #[Groups(['position_classification', 'position_classification:write'])]
    public float $correction = 0.0;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    #[Assert\Length(max: 500)]
    #[Groups(['position_classification', 'position_classification:write'])]
    public ?string $comment = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['position_classification'])]
    public ?float $previousYearCorrectedTotal = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['position_classification', 'position_classification:write'])]
    public ?float $budget = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['position_classification', 'position_classification:write'])]
    public ?float $reforecast = null;

    /**
     * @var Collection<Position>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\Position', inversedBy: 'positionClassifications')]
    #[Groups(['position_classification', 'position_classification:write'])]
    private Collection $positions;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['position_classification'])]
    private int $id;

    public function __construct()
    {
        $this->positions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<Position>
     */
    public function getPositions(): Collection
    {
        return $this->positions;
    }

    public function addPosition(Position $position): self
    {
        if (!$this->positions->contains($position)) {
            $this->positions->add($position);
        }

        return $this;
    }

    public function removePosition(Position $position): self
    {
        if ($this->positions->contains($position)) {
            $this->positions->removeElement($position);
        }

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if (null === ($region = $this->businessUnit->getRegion()) || null === ($subdivision = $region->getSubDivision()) || !$this->positionCategory->getDivisions()->contains($subdivision->division)) {
            $context
                ->buildViolation('This business unit is not active for this position category')
                ->atPath('positionCategory')
                ->addViolation()
            ;
        }
    }
}
