<?php

declare(strict_types=1);

namespace App\Entity\Sales\MarketIntelligence;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'market_intelligence_files')]
#[App\Loggable(owner: 'marketIntelligence', ownerRelation: 'marketIntelligenceFiles')]
class MarketIntelligenceFile extends File
{
    #[ORM\ManyToOne(targetEntity: MarketIntelligence::class, inversedBy: 'marketIntelligenceFiles')]
    private ?MarketIntelligence $marketIntelligence = null;

    public function getMarketIntelligence(): MarketIntelligence
    {
        return $this->marketIntelligence;
    }

    public function setMarketIntelligence(MarketIntelligence $marketIntelligence): self
    {
        $this->marketIntelligence = $marketIntelligence;

        return $this;
    }
}
