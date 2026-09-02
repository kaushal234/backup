<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing\Shipments;

use Symfony\Component\Serializer\Attribute\Groups;

abstract class Ship
{
    #[Groups(['shipment'])]
    public string $type;
    #[Groups(['shipment'])]
    public string $code;
    #[Groups(['shipment'])]
    public string $address;
    #[Groups(['shipment'])]
    public \DateTime $plannedDeliveryDate;
}
