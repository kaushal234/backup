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
#[ORM\Table(name: 'customer_logo_files')]
#[App\Loggable(owner: 'customer', ownerRelation: 'files')]
class CustomerLogoFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer', inversedBy: 'files')]
    private ?Customer $customer = null;

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }
}
