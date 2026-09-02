<?php

declare(strict_types=1);

namespace App\Entity\Purchasing\SupplierRanking;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'supplier_rankings_notations')]
#[ApiResource]
class Notation
{
    #[ORM\Id, ORM\ManyToOne(targetEntity: SupplierRanking::class, inversedBy: 'notations'), ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ApiProperty(identifier: true)]
    public SupplierRanking $supplierRanking;

    #[ORM\Id, ORM\ManyToOne(targetEntity: Criteria::class)]
    #[ApiProperty(identifier: true)]
    #[Groups(['notation', 'notation:update'])]
    public Criteria $criteria;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Groups(['notation', 'notation:update'])]
    public ?int $notation = null;

    /**
     * Revenue should be the turnover of the last 12 months.
     */
    #[Groups('notation')]
    public function isEnabled(): bool
    {
        return $this->supplierRanking->revenue >= $this->criteria->minTurnover;
    }

    /**
     * Get ranking color depending on thresholds criteria.
     * Rank limit of threshold criterias are ordered from lowest to highest rank.
     * So if notation is lower to rank limit, we can return the color corresponding from classification.
     * Todo: To be rework, it is not working properly on get collection.
     */
    #[Groups('notation')]
    public function getRankingColor(): ?string
    {
        $rankingColor = null;

        /** @var ThresholdCriteria $thresholdsCriteria */
        foreach ($this->criteria->getThresholdsCriterias() as $thresholdsCriteria) {
            if ($this->notation <= $thresholdsCriteria->rankLimit) {
                return $thresholdsCriteria->threshold->classification->color;
            }
        }

        return null;
    }
}
