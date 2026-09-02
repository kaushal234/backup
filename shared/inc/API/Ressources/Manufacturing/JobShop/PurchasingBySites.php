<?php

declare(strict_types=1);

namespace Shared\Ressources\Manufacturing\JobShop;

use Shared\Ressources\User;

class PurchasingBySites
{
    public int $site;
    public float $price;
    public string $currency;
    public string|User|null $buyer = null;
    public int $leadTime;
    public string $leadTimeUnit;
    public string $mainSupplier;
    public float $standardCost;
    public string $costCurrency;
    public float $priceMainCurrency;
    public float $standardCostMainCurrency;
    public float $gap;
    public int|float $gapPercent;
}