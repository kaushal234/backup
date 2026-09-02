<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\EngineeringRevisionTrait;
use App\ION\Resources\Manufacturing\JobShop\ItemInterface;
use App\ION\Resources\Manufacturing\JobShop\ItemTrait;
use Symfony\Component\Serializer\Attribute\Groups;

class ViewItems implements ItemInterface
{
    use EngineeringRevisionTrait;
    use ItemTrait;

    #[Groups(['cbom'])]
    public int $position;

    #[Groups(['cbom'])]
    public int $operation;

    #[Groups(['cbom'])]
    public int $level;

    #[Groups(['cbom'])]
    public ?string $partNumberProject = null;

    #[Groups(['cbom'])]
    public ?string $engineeringSignalCode;

    #[Groups(['cbom'])]
    public ?string $extraInformation = null;

    #[Groups(['cbom'])]
    public bool $customized = false;

    #[Groups(['cbom'])]
    public ?string $engineeringSelectionCode = null;

    #[Groups(['cbom'])]
    public ?float $productQuantity = null;
}
