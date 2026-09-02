<?php

declare(strict_types=1);

namespace App\Manager\Parts;

use App\Entity\Parts\SparePartsRequest;
use App\ION\Manager\Warehousing\ShipmentManager;

readonly class SparePartsRequestManager
{
    public function __construct(
        private ShipmentManager $shipmentManager,
    ) {
    }

    public function setShipments(SparePartsRequest $sparePartsRequest): SparePartsRequest
    {
        if (null === $salesOrder = $sparePartsRequest->salesOrder) {
            return $sparePartsRequest;
        }

        $shipments = $this->shipmentManager->getShipmentsBySalesOrder($salesOrder);
        foreach ($shipments as $shipment) {
            $sparePartsRequest->processShipment($shipment);
        }

        return $sparePartsRequest;
    }
}
