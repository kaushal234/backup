<?php

declare(strict_types=1);

namespace App\Entity\MIS\TroubleTicket;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'trouble_ticket_files')]
#[App\Loggable(owner: 'troubleTicket', ownerRelation: 'files')]
class TroubleTicketFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\MIS\TroubleTicket\TroubleTicket', inversedBy: 'files')]
    private ?TroubleTicket $troubleTicket = null;

    public function getTroubleTicket(): ?TroubleTicket
    {
        return $this->troubleTicket;
    }

    public function setTroubleTicket(?TroubleTicket $troubleTicket): self
    {
        $this->troubleTicket = $troubleTicket;

        return $this;
    }
}
