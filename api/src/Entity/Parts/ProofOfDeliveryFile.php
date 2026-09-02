<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'spare_parts_requests_proof_of_delivery_files')]
#[App\Loggable(owner: 'sparePartsRequest', ownerRelation: 'files')]
class ProofOfDeliveryFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Parts\SparePartsRequest', inversedBy: 'files')]
    public ?SparePartsRequest $sparePartsRequest = null;
}
