<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'competitor_pricings_files')]
#[App\Loggable(owner: 'competitorPricing', ownerRelation: 'competitorPricingFiles')]
class CompetitorPricingFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\CompetitorPricing', inversedBy: 'competitorPricingFiles')]
    private ?CompetitorPricing $competitorPricing = null;

    public function getCompetitorPricing(): CompetitorPricing
    {
        return $this->competitorPricing;
    }

    public function setCompetitorPricing(CompetitorPricing $competitorPricing): self
    {
        $this->competitorPricing = $competitorPricing;

        return $this;
    }
}
