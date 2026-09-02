<?php

declare(strict_types=1);

namespace App\ION\Resources\Procurement\Orders;

use Symfony\Component\Serializer\Attribute\Groups;

class Amount
{
    #[Groups(['amount'])]
    public float $value;

    #[Groups(['amount'])]
    public string $currency;

    #[Groups(['amount'])]
    public ?string $unitOfMeasure;
}
