<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing;

use Symfony\Component\Serializer\Attribute\Groups;

class Material
{
    #[Groups(['material_list'])]
    public string $position;

    #[Groups(['material_list'])]
    public string $operation;

    #[Groups(['material_list'])]
    public string $item;

    #[Groups(['material_list'])]
    public string $itemDescription;

    #[Groups(['material_list'])]
    public string $itemOtherDescription;

    #[Groups(['material_list'])]
    public string $warehouse;

    #[Groups(['material_list'])]
    public float $netQuantity;

    #[Groups(['material_list'])]
    public float $estimatedQuantity;

    #[Groups(['material_list'])]
    public float $actualQuantity;

    #[Groups(['material_list'])]
    public string $unitOfMeasure;

    #[Groups(['material_list'])]
    public string $revision;

    #[Groups(['material_list'])]
    public string $reportMaterial;

    #[Groups(['material_list'])]
    public int $inventoryOnHand = 0;

    #[Groups(['material_list'])]
    public int $inventoryOnOrder = 0;

    #[Groups(['material_list'])]
    public ?float $costPrice = null;

    #[Groups(['material_list'])]
    public string $currency;
}
