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
#[ORM\Table(name: 'tracking_files')]
#[App\Loggable(owner: 'tracking', ownerRelation: 'files')]
class TrackingFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Parts\Tracking', inversedBy: 'files')]
    public ?Tracking $tracking = null;
}
