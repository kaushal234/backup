<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing\Shipments;

use Symfony\Component\Serializer\Attribute\Groups;

class ShippingDocument
{
    #[Groups(['shipment'])]
    public string $route;
    #[Groups(['shipment'])]
    public string $deliveryTerms;
    #[Groups(['shipment'])]
    public string $pointOfTitlePassage;
    #[Groups(['shipment'])]
    public string $estimatedFreightCosts;
    #[Groups(['shipment'])]
    public string $estimatedFreightCostsCurrency;
}
