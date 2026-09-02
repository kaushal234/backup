<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\ItemInterface;
use App\ION\Resources\Manufacturing\JobShop\ItemTrait;
use App\ION\Resources\Manufacturing\JobShop\PMOCTrait;
use Symfony\Component\Serializer\Attribute\Groups;

class ShopfloorPdfItem implements ItemInterface
{
    use ItemTrait;
    use PMOCTrait;

    #[Groups(['bom'])]
    public int $position;
}
