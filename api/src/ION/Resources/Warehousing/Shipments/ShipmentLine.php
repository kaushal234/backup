<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing\Shipments;

use Symfony\Component\Serializer\Attribute\Groups;

class ShipmentLine
{
    #[Groups(['shipment'])]
    public string $item;

    #[Groups(['shipment'])]
    public string $itemDescription;

    #[Groups(['shipment'])]
    public float $shippedQuantity;

    #[Groups(['shipment'])]
    public string $unitOfMeasure;

    #[Groups(['shipment'])]
    public OrderReference $orderReference;

    private Shipment $shipment;

    public function getShipment(): Shipment
    {
        return $this->shipment;
    }

    public function setShipment(Shipment $shipment): self
    {
        $this->shipment = $shipment;

        return $this;
    }
}
