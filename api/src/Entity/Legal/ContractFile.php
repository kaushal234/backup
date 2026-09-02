<?php

declare(strict_types=1);

namespace App\Entity\Legal;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'contract_files')]
#[App\Loggable(owner: 'contract', ownerRelation: 'files')]
class ContractFile extends File
{
    #[ORM\ManyToOne(targetEntity: Contract::class, inversedBy: 'files')]
    public ?Contract $contract = null;
}
