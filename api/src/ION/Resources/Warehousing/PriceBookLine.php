<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing;

use Symfony\Component\Serializer\Attribute\Groups;

class PriceBookLine
{
    #[Groups(['inventory'])]
    public float $value;

    #[Groups(['inventory'])]
    public string $currency;

    #[Groups(['inventory'])]
    public ?string $effectiveDate = null;

    #[Groups(['inventory'])]
    public ?string $expiryDate;
}
