<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing\Shipments;

use Symfony\Component\Serializer\Attribute\Groups;

class References
{
    #[Groups(['shipment'])]
    public string $shipmentReference;
    #[Groups(['shipment'])]
    public string $customerOrder;
}
