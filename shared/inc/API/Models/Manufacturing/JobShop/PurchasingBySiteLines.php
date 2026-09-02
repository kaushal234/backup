<?php

declare(strict_types=1);

namespace Shared\Models\Manufacturing\JobShop;

use Shared\Ressources\User;

class PurchasingBySiteLines
{
    public function __construct(
        public int $site,
        public float $price,
        public string $currency,
        public int $leadTime,
        public string $mainSupplier,
        public float $standardCost,
        public string $costCurrency,
        public float $priceMainCurrency,
        public float $standardCostMainCurrency,
        public float $gap,
        public int|float $gapPercent,
        public string|User|null $buyer = null,
    ) {
    }
}