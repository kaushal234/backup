<?php

declare(strict_types=1);

namespace App\Dto\Purchasing\Supplier;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class SupplierLocationInput
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 3)]
    #[Groups(['supplier:write'])]
    public string $locationCode;

    #[Groups(['supplier:write'])]
    public ?int $buyerId = null;
}
