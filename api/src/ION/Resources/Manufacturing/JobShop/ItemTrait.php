<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use Symfony\Component\Serializer\Attribute\Groups;

trait ItemTrait
{
    #[Groups(['ion:item'])]
    public string $partNumber;

    #[Groups(['ion:item'])]
    public string $itemDescription;

    #[Groups(['ion:item'])]
    public string $itemOtherDescription;

    #[Groups(['ion:item'])]
    public float $quantity;

    #[Groups(['ion:item'])]
    public ?float $productQuantity = null;

    #[Groups(['ion:item'])]
    public string $unitOfMeasure;
}
