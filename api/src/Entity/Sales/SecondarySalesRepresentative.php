<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['customer']],
    denormalizationContext: ['groups' => ['customer_write']]
)]
#[App\Loggable(owner: 'customer', ownerRelation: 'secondarySalesRepresentatives')]
class SecondarySalesRepresentative extends AbstractSalesRepresentative
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer', inversedBy: 'secondarySalesRepresentatives')]
    public Customer $customer;
}
