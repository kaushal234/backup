<?php

declare(strict_types=1);

namespace App\Entity\Purchasing\SupplierRanking;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Simple rule link to main Threshold entity.
 * This class compare supplier rank of criteria to this rank limit.
 * If the supplier rank is below or equal to rankLimit, it's matching the rule.
 */
#[ORM\Entity]
#[ORM\Table(name: 'supplier_rankings_thresholds_criterias')]
#[ApiResource]
class ThresholdCriteria
{
    #[ORM\Id, ORM\ManyToOne(targetEntity: Threshold::class, inversedBy: 'thresholdsCriterias')]
    public Threshold $threshold;

    #[ORM\Id, ORM\ManyToOne(targetEntity: Criteria::class, inversedBy: 'thresholdsCriterias')]
    #[Groups('threshold_criteria')]
    public Criteria $criteria;

    #[ORM\Column(type: 'smallint')]
    #[Groups('threshold_criteria')]
    public int $rankLimit;
}
