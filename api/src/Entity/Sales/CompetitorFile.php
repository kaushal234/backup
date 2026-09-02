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
#[ORM\Table(name: 'competitors_files')]
#[App\Loggable(owner: 'competitor', ownerRelation: 'competitorFiles')]
class CompetitorFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Competitor', inversedBy: 'competitorFiles')]
    private ?Competitor $competitor = null;

    public function getCompetitor(): Competitor
    {
        return $this->competitor;
    }

    /**
     * @return $this
     */
    public function setCompetitor(Competitor $competitor)
    {
        $this->competitor = $competitor;

        return $this;
    }
}
