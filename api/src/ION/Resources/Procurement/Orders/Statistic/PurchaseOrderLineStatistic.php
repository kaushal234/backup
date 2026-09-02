<?php

declare(strict_types=1);

namespace App\ION\Resources\Procurement\Orders\Statistic;

use Symfony\Component\Serializer\Attribute\Groups;

class PurchaseOrderLineStatistic
{
    #[Groups(['purchase_order_statistic'])]
    public string $orderIdentifier;

    #[Groups(['purchase_order_statistic'])]
    public string $lineIdentifier;

    #[Groups(['purchase_order_statistic'])]
    public string $sequence;

    #[Groups(['purchase_order_statistic'])]
    public bool $late;

    #[Groups(['purchase_order_statistic'])]
    public bool $unconfirmed;
}
