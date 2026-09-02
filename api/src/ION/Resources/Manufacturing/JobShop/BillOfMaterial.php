<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use Symfony\Component\Serializer\Attribute\Groups;

class BillOfMaterial
{
    #[Groups(['cbom'])]
    public string $code;

    #[Groups(['cbom'])]
    public string $revision;

    #[Groups(['cbom'])]
    public string $effectiveDate;

    #[Groups(['cbom'])]
    public string $expiryDate;
}
