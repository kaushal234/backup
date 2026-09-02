<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\ItemInterface;
use Symfony\Component\Serializer\Attribute\Groups;

class PartNumberListItems implements ItemInterface
{
    #[Groups(['cbom'])]
    public string $partNumber;
}
