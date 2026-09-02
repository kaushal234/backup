<?php

declare(strict_types=1);

namespace App\SageParts\Resources;

use Symfony\Component\Serializer\Attribute\Groups;

class Quantity
{
    #[Groups(['sage_part'])]
    public string $unit;

    #[Groups(['sage_part'])]
    public float $quantity;
}
