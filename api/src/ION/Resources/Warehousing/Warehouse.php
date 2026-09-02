<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing;

use Symfony\Component\Serializer\Attribute\Groups;

class Warehouse
{
    #[Groups(['inventory'])]
    public string $code;

    #[Groups(['inventory'])]
    public string $name;

    #[Groups(['inventory'])]
    public float $reorderPoint;

    #[Groups(['inventory'])]
    public float $safetyStock;

    #[Groups(['inventory'])]
    public float $itemSafety;

    #[Groups(['inventory'])]
    public float $inventoryOnHand = 0.0;

    #[Groups(['inventory'])]
    public float $inventoryOnOrder = 0.0;

    #[Groups(['inventory'])]
    public float $allocated = 0.0;

    #[Groups(['inventory'])]
    public string $purchaseCurrency;

    #[Groups(['inventory'])]
    public float $purchasePrice;

    #[Groups(['inventory'])]
    public ?\DateTime $lastPurchasePriceDate = null;

    #[Groups(['inventory'])]
    public string $standardCurrency;

    #[Groups(['inventory'])]
    public float $standardCost;

    #[Groups(['inventory'])]
    public bool $includeInEnterprisePlanning;

    #[Groups(['inventory'])]
    public function getAvailable(): float
    {
        return $this->inventoryOnHand - max($this->allocated, 0);
    }
}
