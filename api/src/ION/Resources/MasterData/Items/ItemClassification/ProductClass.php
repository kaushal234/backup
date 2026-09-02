<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\Items\ItemClassification;

use Symfony\Component\Serializer\Attribute\Groups;

class ProductClass
{
    #[Groups(['product:class'])]
    public $code;

    #[Groups(['product:class'])]
    public $name;
}
