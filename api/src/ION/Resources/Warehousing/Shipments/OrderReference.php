<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing\Shipments;

use Symfony\Component\Serializer\Attribute\Groups;

class OrderReference
{
    final public const SALES_ORDER_ORIGINS = [1, 2, 3];

    #[Groups(['shipment'])]
    public int $type;

    #[Groups(['shipment'])]
    public string $order;

    #[Groups(['shipment'])]
    public int $position;

    #[Groups(['shipment'])]
    public int $sequence;

    public function isSalesOrder(): bool
    {
        return \in_array($this->type, self::SALES_ORDER_ORIGINS, true);
    }
}
