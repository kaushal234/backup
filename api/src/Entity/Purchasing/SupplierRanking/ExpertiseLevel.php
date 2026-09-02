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
use App\Controller\Purchasing\ExpertiseLevelTransferController;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'supplier_rankings_expertise_levels')]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('FEATURE_SUPPLIER_RANKING_EXPERTISE_LEVEL_READ')"),
        new Post(),
        new Get(security: "is_granted('FEATURE_SUPPLIER_RANKING_EXPERTISE_LEVEL_READ')"),
        new Delete(),
        new Put(
            uriTemplate: '/expertise_levels/{id}/transfer',
            controller: ExpertiseLevelTransferController::class,
            deserialize: false,
            name: 'transfer_expertise_level'
        ),
        new Put(),
    ],
    routePrefix: 'purchasing/supplier_ranking',
    normalizationContext: ['groups' => ['expertise_level']],
    denormalizationContext: ['groups' => ['expertise_level:write']],
    order: ['name'],
    security: "is_granted('FEATURE_SUPPLIER_RANKING_ADMIN')",
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name'])]
class ExpertiseLevel implements \Stringable
{
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 100)]
    #[Groups(['expertise_level', 'expertise_level_list', 'expertise_level:write', 'periodicity'])]
    public string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[Groups(['expertise_level', 'expertise_level:write'])]
    public ?string $description = null;

    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['expertise_level', 'expertise_level_list'])]
    private int $id;

    /**
     * @var Collection<Periodicity>
     */
    #[ORM\OneToMany(mappedBy: 'expertiseLevel', targetEntity: Periodicity::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $periodicityByClassifications;

    /**
     * @var Collection<Threshold>
     */
    #[ORM\OneToMany(mappedBy: 'expertiseLevel', targetEntity: Threshold::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $thresholds;

    public function __construct()
    {
        $this->periodicityByClassifications = new ArrayCollection();
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
     * @return Collection<Periodicity>
     */
    public function getPeriodicityByClassifications(): Collection
    {
        return $this->periodicityByClassifications;
    }

    public function addPeriodicityByClassifications(Periodicity $periodicity): self
    {
        if (!$this->periodicityByClassifications->contains($periodicity)) {
            $this->periodicityByClassifications->add($periodicity);
        }

        return $this;
    }

    public function removePeriodicityByClassifications(Periodicity $periodicity): self
    {
        if ($this->periodicityByClassifications->contains($periodicity)) {
            $this->periodicityByClassifications->removeElement($periodicity);
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
