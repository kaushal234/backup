<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\ItemInterface;
use App\ION\Resources\Manufacturing\JobShop\ItemTrait;
use App\ION\Resources\Manufacturing\JobShop\PMOCTrait;
use Symfony\Component\Serializer\Attribute\Groups;

class MaterialsCsvItem implements ItemInterface
{
    use ItemTrait;
    use PMOCTrait;

    #[Groups(['cbom'])]
    public string $engineeringSignalCode;

    #[Groups(['cbom'])]
    public string $engineeringRevision;

    #[Groups(['cbom'])]
    public string $itemType;

    #[Groups(['cbom'])]
    public string $purchaseStatisticsGroup;

    #[Groups(['cbom'])]
    public float $orderQuantityIncrement;

    #[Groups(['cbom'])]
    public float $minimumOrderQuantity;

    #[Groups(['cbom'])]
    public float $safetyStock;

    #[Groups(['cbom'])]
    public float $supplyTime;

    #[Groups(['cbom'])]
    public string $buyFromBusinessPartner;

    #[Groups(['cbom'])]
    public string $buyer;

    #[Groups(['cbom'])]
    public float $estimatedStandardCost;

    #[Groups(['cbom'])]
    public string $salesPriceGroup;

    #[Groups(['cbom'])]
    public string $itemGroup;
}
