<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use App\ION\Resources\MasterData\CodeDefinitions\LogisticCodes\Carrier;
use App\ION\Resources\Warehousing\Shipments\ShipmentLine;
use Symfony\Component\Serializer\Attribute\Groups;

class PartTracking
{
    #[Groups(['part'])]
    public string $trackingNumber;

    #[Groups(['part'])]
    public string $carrier = 'Unknown';

    #[Groups(['part'])]
    public float $shippedQuantity;

    #[Groups(['part'])]
    public string $unitOfMeasure;

    #[Groups(['part'])]
    public ?string $trackingLink = null;

    private function __construct(string $trackingNumber, ?Carrier $carrier, float $shippedQuantity, string $unitOfMeasure)
    {
        $this->trackingNumber = $trackingNumber;
        if (null !== $carrier) {
            $this->carrier = $carrier->name;
            if (null !== $carrier->url) {
                $this->trackingLink = $carrier->url.$trackingNumber;
            }
        }
        $this->shippedQuantity = $shippedQuantity;
        $this->unitOfMeasure = $unitOfMeasure;
    }

    public static function fromShipmentLine(ShipmentLine $shipmentLine): self
    {
        $load = $shipmentLine->getShipment()->load;

        return new self(
            $load->trackingNumber,
            $load->carrier,
            $shipmentLine->shippedQuantity,
            $shipmentLine->unitOfMeasure
        );
    }
}
