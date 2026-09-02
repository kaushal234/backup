<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(operations: [])]
#[ORM\Table(name: 'trucks')]
class Truck
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Archived\TruckType')]
    #[ORM\JoinColumn(nullable: false)]
    private TruckType $truckType;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Archived\ShippingQuotationRequestLine', inversedBy: 'trucks')]
    #[ORM\JoinColumn(nullable: false)]
    private ShippingQuotationRequestLine $shippingQuotationRequestLine;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTruckType(): TruckType
    {
        return $this->truckType;
    }

    public function setTruckType(TruckType $truckType): self
    {
        $this->truckType = $truckType;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getShippingQuotationRequestLine(): ShippingQuotationRequestLine
    {
        return $this->shippingQuotationRequestLine;
    }

    public function setShippingQuotationrequestLine(ShippingQuotationRequestLine $shippingQuotationRequestLine): self
    {
        $this->shippingQuotationRequestLine = $shippingQuotationRequestLine;

        return $this;
    }
}
