<?php

declare(strict_types=1);

namespace App\SageParts\Resources;

use Symfony\Component\Serializer\Attribute\Groups;

class Location
{
    #[Groups(['sage_part'])]
    public string $name;

    #[Groups(['sage_part'])]
    public Quantity $onHand;

    #[Groups(['sage_part'])]
    public Quantity $onOrder;

    #[Groups(['sage_part'])]
    public Price $unitPrice;
}
