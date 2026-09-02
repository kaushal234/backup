<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\EngineeringRevisionTrait;
use App\ION\Resources\Manufacturing\JobShop\ItemInterface;
use App\ION\Resources\Manufacturing\JobShop\ItemTrait;
use Symfony\Component\Serializer\Attribute\Groups;

class ShopfloorViewItem implements ItemInterface
{
    use EngineeringRevisionTrait;
    use ItemTrait;

    #[Groups(['bom'])]
    public int $level;

    #[Groups(['bom'])]
    public int $position;

    #[Groups(['bom'])]
    public string $engineeringSignalCode;

    #[Groups(['bom'])]
    public string $extraInformation;

    #[Groups(['bom'])]
    public int $operation;

    #[Groups(['bom'])]
    public string $warehouse;

    #[Groups(['bom'])]
    public bool $backflushIfMaterial;

    #[Groups(['bom'])]
    public bool $phantom;
}
