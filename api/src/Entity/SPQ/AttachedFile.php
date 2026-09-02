<?php

declare(strict_types=1);

namespace App\Entity\SPQ;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'spq_attached_files')]
#[App\Loggable(owner: 'quotation', ownerRelation: 'attachedFiles')]
class AttachedFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\SPQ\Quotation', inversedBy: 'attachedFiles')]
    private ?Quotation $quotation = null;

    public function getQuotation(): Quotation
    {
        return $this->quotation;
    }

    public function setQuotation(Quotation $quotation): self
    {
        $this->quotation = $quotation;

        return $this;
    }
}
