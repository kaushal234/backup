<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing\Shipments;

use App\ION\Resources\MasterData\CodeDefinitions\LogisticCodes\Carrier;
use Symfony\Component\Serializer\Attribute\Groups;

class Load
{
    #[Groups(['shipment'])]
    public string $loadCode;
    #[Groups(['shipment'])]
    public string $route;
    #[Groups(['shipment'])]
    public string $status;
    #[Groups(['shipment'])]
    public string $plannedDeliveryDate;
    #[Groups(['shipment'])]
    public ?string $trackingNumber = null;
    #[Groups(['shipment'])]
    public ?Carrier $carrier = null;
}
