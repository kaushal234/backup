<?php

declare(strict_types=1);

namespace App\Entity\Purchasing\Supplier;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'suppliers_location')]
class SupplierLocation
{
    #[ORM\Id, ORM\ManyToOne(targetEntity: Supplier::class, inversedBy: 'buyFrom')]
    public Supplier $supplier;

    #[ORM\Id, ORM\ManyToOne(targetEntity: Location::class)]
    #[Groups(['supplier'])]
    public Location $location;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[Groups(['supplier'])]
    public ?People $buyer = null;
}
