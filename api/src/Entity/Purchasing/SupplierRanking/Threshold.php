<?php

declare(strict_types=1);

namespace App\Entity\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\Mapping\Attributes\Transferable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Main rules configuration to automatically manage classification of supplier depending on his notations and thresholds.
 * If the supplier ranks match to a rule, we update supplier ranking to the lowest target classification.
 * The lowest classification is determined by his workflow level.
 * Count of thresholdsCriterias should be superior to minCriterias for a match.
 */
#[ORM\Entity]
#[ORM\Table(name: 'supplier_rankings_thresholds')]
#[ApiResource(
    operations: [new GetCollection()],
    routePrefix: '/purchasing/supplier_ranking',
    normalizationContext: ['groups' => ['threshold', 'classification_list', 'criteria', 'notation', 'threshold_criteria']],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'classification' => 'exact',
    'expertiseLevel' => 'exact',
])]
#[ApiFilter(BooleanFilter::class, properties: ['showInGraph'])]
#[ApiFilter(OrderFilter::class, properties: ['classification.workflowLevel'])]
class Threshold implements \Stringable
{
    /**
     * Target classification.
     */
    #[ORM\ManyToOne(targetEntity: Classification::class, inversedBy: 'thresholds'), ORM\JoinColumn(nullable: false)]
    #[Transferable(manager: 'manager.classification')]
    #[Groups('threshold')]
    public Classification $classification;

    #[ORM\ManyToOne(targetEntity: ExpertiseLevel::class, inversedBy: 'thresholds'), ORM\JoinColumn(nullable: false)]
    #[Transferable(manager: 'manager.expertise_level')]
    #[Groups('threshold')]
    public ExpertiseLevel $expertiseLevel;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank, Assert\Length(min: 3, max: 100)]
    #[Groups('threshold')]
    public string $name;

    /**
     * Number of minimum matching criterias.
     */
    #[ORM\Column(type: 'smallint', options: ['default' => 1])]
    #[Groups('threshold')]
    public int $minCriterias = 1;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[Groups('threshold')]
    public ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups('threshold')]
    public bool $showInGraph = false;

    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups('threshold')]
    private int $id;

    /**
     * @var Collection<ThresholdCriteria>
     */
    #[ORM\OneToMany(mappedBy: 'threshold', targetEntity: ThresholdCriteria::class, cascade: ['remove'], orphanRemoval: true)]
    #[Groups('threshold')]
    private Collection $thresholdsCriterias;

    public function __construct()
    {
        $this->thresholdsCriterias = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<ThresholdCriteria>
     */
    public function getThresholdsCriterias(): Collection
    {
        return $this->thresholdsCriterias;
    }

    public function addThresholdsCriterias(ThresholdCriteria $thresholdCriteria): self
    {
        if (!$this->thresholdsCriterias->contains($thresholdCriteria)) {
            $this->thresholdsCriterias->add($thresholdCriteria);
        }

        return $this;
    }

    public function removeThresholdsCriterias(ThresholdCriteria $thresholdCriteria): self
    {
        if ($this->thresholdsCriterias->contains($thresholdCriteria)) {
            $this->thresholdsCriterias->removeElement($thresholdCriteria);
        }

        return $this;
    }
}
