<?php

declare(strict_types=1);

namespace App\SageParts\Resources;

use Symfony\Component\Serializer\Attribute\Groups;

class Price
{
    #[Groups(['sage_part'])]
    public string $currency;

    #[Groups(['sage_part'])]
    public float $price;
}
