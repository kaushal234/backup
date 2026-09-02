<?php

declare(strict_types=1);

namespace App\ION\Resources\Procurement\Orders;

use Symfony\Component\Serializer\Attribute\Groups;

class Quantity
{
    #[Groups(['quantity'])]
    public float $value;

    #[Groups(['quantity'])]
    public ?string $unitOfMeasure;
}
