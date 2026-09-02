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
#[ORM\Table(name: 'sales_orders_files')]
#[App\Loggable(owner: 'order', ownerRelation: 'files')]
class OrderFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Order', inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;

        return $this;
    }
}
