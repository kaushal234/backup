<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\BillOfMaterials;

use Symfony\Component\Serializer\Attribute\Groups;

class PurchasingBenchmarkBySite
{
    #[Groups(['bom'])]
    public int $site;

    #[Groups(['bom'])]
    public float $price;

    #[Groups(['bom'])]
    public string $currency;

    #[Groups(['bom'])]
    public string $buyer;

    #[Groups(['bom'])]
    public int $leadTime;

    #[Groups(['bom'])]
    public string $leadTimeUnit;

    #[Groups(['bom'])]
    public string $mainSupplier;

    #[Groups(['bom'])]
    public float $standardCost;

    #[Groups(['bom'])]
    public string $costCurrency;
}
