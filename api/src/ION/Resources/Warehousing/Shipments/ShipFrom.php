<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing\Shipments;

use Symfony\Component\Serializer\Attribute\Groups;

class ShipFrom extends Ship
{
    #[Groups(['shipment'])]
    public \DateTime $plannedDeliveryDate;
}
