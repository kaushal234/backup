<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(operations: [])]
class TruckType extends TransportType
{
}
