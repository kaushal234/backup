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
    normalizationContext: ['groups' => ['customer', 'sales_representative_campaign']],
    denormalizationContext: ['groups' => ['customer_write']]
)]
#[App\Loggable(owner: 'customer', ownerRelation: 'mainSalesRepresentative')]
class MainSalesRepresentative extends AbstractSalesRepresentative
{
    #[ORM\OneToOne(mappedBy: 'mainSalesRepresentative', targetEntity: 'App\Entity\Sales\Customer')]
    public Customer $customer;
}
