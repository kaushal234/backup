<?php

declare(strict_types=1);

namespace App\Dto\Purchasing\Supplier;

use App\Entity\Purchasing\Supplier\Supplier;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class SupplierInput
{
    #[Assert\NotBlank]
    #[Groups(['supplier:write'])]
    public string $name;

    #[Assert\NotBlank]
    #[Groups(['supplier:write'])]
    public string $code;

    #[Assert\AtLeastOneOf([new Assert\Blank(), new Assert\Length(min: 3, max: 3)])]
    #[Groups(['supplier:write'])]
    public ?string $masterBusinessUnit = null;

    #[Groups(['supplier:write'])]
    public ?string $country = null;

    #[Groups(['supplier:write'])]
    public ?int $buyer = null;

    #[Assert\Choice(choices: [Supplier::ACTIVE, Supplier::INACTIVE, Supplier::DELETED])]
    #[Groups(['supplier:write'])]
    public ?string $status = null;

    #[Groups(['supplier:write'])]
    public ?string $currency = null;

    /**
     * @var SupplierLocationInput[]
     */
    #[Assert\Valid]
    #[Groups(['supplier:write'])]
    public ?array $buyFrom = [];
}
