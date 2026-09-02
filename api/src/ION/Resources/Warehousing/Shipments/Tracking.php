<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing\Shipments;

use Symfony\Component\Serializer\Attribute\Groups;

class Tracking
{
    #[Groups(['shipment'])]
    public string $carrierTrackingNumber;
    #[Groups(['shipment'])]
    public string $trackingNumber;
}
