<?php

declare(strict_types=1);

namespace App\Entity\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'supplier_rankings_criterias')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'purchasing/supplier_ranking',
    order: ['name'],
    security: "is_granted('FEATURE_SUPPLIER_RANKING_CRITERIA_READ')"
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name'])]
class Criteria implements \Stringable
{
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank, Assert\Length(min: 3, max: 100)]
    #[Groups('criteria')]
    public string $name;

    /**
     * Criteria is disabled depending on the supplier turnover of the last 12 months.
     */
    #[ORM\Column(type: 'integer')]
    public int $minTurnover = 0;

    /**
     * Indicate if the criteria should be public or private.
     * Evendors do not show private criterias.
     */
    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    #[Groups('criteria')]
    public bool $public = true;

    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups('criteria')]
    private int $id;

    /**
     * OrderBy is important here to get ranking color.
     *
     * @see Notation::getRankingColor()
     */
    #[ORM\OneToMany(mappedBy: 'criteria', targetEntity: ThresholdCriteria::class)]
    #[ORM\OrderBy(['rankLimit' => 'ASC'])]
    #[Ignore]
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

    public function getThresholdsCriterias(): Collection
    {
        return $this->thresholdsCriterias;
    }
}
