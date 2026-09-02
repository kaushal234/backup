<?php

declare(strict_types=1);

namespace App\SageParts\Factory\Items;

use App\SageParts\Models\Items\Item;

class ItemFactory
{
    public function create(string $identifier): Item
    {
        $item = new Item();

        return $item
            ->setMasterID($identifier)
            ->setQuantity(1)
            ->setUnit('EACH')
            ->setLineNumber(1)
        ;
    }
}
