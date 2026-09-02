<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use Symfony\Component\Serializer\Attribute\Groups;

trait PurchasingTrait
{
    #[Groups(['bom:purchasing'])]
    public ?string $supplySource = null;

    #[Groups(['bom:purchasing'])]
    public string $supplier;

    #[Groups(['bom:purchasing'])]
    public string $supplierName;

    #[Groups(['bom:purchasing'])]
    public int $leadTime;

    #[Groups(['bom:purchasing'])]
    public ?string $leadTimeUnit = null;

    #[Groups(['bom:purchasing'])]
    public int $inventoryOnHand;

    #[Groups(['bom:purchasing'])]
    public int $inventoryOnOrder;

    #[Groups(['bom:purchasing'])]
    public int $allocated;
}
