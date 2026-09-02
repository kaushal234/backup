<?php

declare(strict_types=1);

namespace App\Entity\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Purchasing\ClassificationTransferController;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'supplier_rankings_classifications')]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('FEATURE_SUPPLIER_RANKING_CLASSIFICATION_READ')"),
        new Post(),
        new Get(security: "is_granted('FEATURE_SUPPLIER_RANKING_CLASSIFICATION_READ')"),
        new Delete(),
        new Put(
            uriTemplate: '/classifications/{id}/transfer',
            controller: ClassificationTransferController::class,
            deserialize: false,
            name: 'transfer_classification',
        ),
        new Put(),
    ],
    routePrefix: '/purchasing/supplier_ranking',
    normalizationContext: ['groups' => ['classification']],
    denormalizationContext: ['groups' => ['classification:write']],
    order: ['name'],
    security: "is_granted('FEATURE_SUPPLIER_RANKING_ADMIN')",
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name'])]
class Classification implements \Stringable
{
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank, Assert\Length(min: 3, max: 100)]
    #[Groups(['classification', 'classification_list', 'classification:write', 'periodicity'])]
    public string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Type(type: 'string'), Assert\Length(max: 65535)]
    #[Groups(['classification', 'classification:write'])]
    public ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['classification', 'classification:write'])]
    public bool $isSupplierApproved = false;

    /**
     * This integer help to know the order of automatic classification status by the worst to the best.
     * This is useful when a user updating notations of supplier ranking.
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['classification', 'classification_list', 'classification:write'])]
    public ?int $workflowLevel = null;

    #[ORM\Column(length: 6, nullable: true)]
    #[Assert\NotBlank, Assert\Length(max: 6)]
    #[Groups(['classification', 'classification_list', 'classification:write'])]
    public ?string $color = null;

    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['classification', 'classification_list'])]
    private int $id;

    /**
     * List of classifications that are allowed to change manually from the current classifications.
     *
     * @var Collection<Classification>
     */
    #[ORM\JoinTable(name: 'supplier_rankings_classification_targets')]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'target_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: self::class)]
    #[Groups(['classification', 'classification:write'])]
    #[MaxDepth(1)]
    private Collection $targetClassifications;

    /**
     * @var Collection<Periodicity>
     */
    #[ORM\OneToMany(mappedBy: 'classification', targetEntity: Periodicity::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $periodicityByExpertiseLevels;

    /**
     * @var Collection<Threshold>
     */
    #[ORM\OneToMany(mappedBy: 'classification', targetEntity: Threshold::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $thresholds;

    public function __construct()
    {
        $this->targetClassifications = new ArrayCollection();
        $this->periodicityByExpertiseLevels = new ArrayCollection();
        $this->thresholds = new ArrayCollection();
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
     * @return Collection<Classification>
     */
    public function getTargetClassifications(): Collection
    {
        return $this->targetClassifications;
    }

    public function addTargetClassification(self $targetClassification): self
    {
        if (!$this->targetClassifications->contains($targetClassification)) {
            $this->targetClassifications->add($targetClassification);
        }

        return $this;
    }

    public function removeTargetClassification(self $targetClassification): self
    {
        if (!$this->targetClassifications->contains($targetClassification)) {
            $this->targetClassifications->removeElement($targetClassification);
        }

        return $this;
    }

    /**
     * @return Collection<Periodicity>
     */
    public function getPeriodicityByExpertiseLevels(): Collection
    {
        return $this->periodicityByExpertiseLevels;
    }

    public function addPeriodicityByExpertiseLevels(Periodicity $periodicity): self
    {
        if (!$this->periodicityByExpertiseLevels->contains($periodicity)) {
            $this->periodicityByExpertiseLevels->add($periodicity);
        }

        return $this;
    }

    public function removePeriodicityByExpertiseLevels(Periodicity $periodicity): self
    {
        if ($this->periodicityByExpertiseLevels->contains($periodicity)) {
            $this->periodicityByExpertiseLevels->removeElement($periodicity);
        }

        return $this;
    }

    /**
     * @return Collection<Threshold>
     */
    public function getThresholds(): Collection
    {
        return $this->thresholds;
    }

    public function addThreshold(Threshold $threshold): self
    {
        if (!$this->thresholds->contains($threshold)) {
            $this->thresholds->add($threshold);
        }

        return $this;
    }

    public function removeThreshold(Threshold $threshold): self
    {
        if ($this->thresholds->contains($threshold)) {
            $this->thresholds->removeElement($threshold);
        }

        return $this;
    }
}
