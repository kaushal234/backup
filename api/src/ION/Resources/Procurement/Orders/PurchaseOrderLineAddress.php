<?php

declare(strict_types=1);

namespace App\ION\Resources\Procurement\Orders;

use Symfony\Component\Serializer\Attribute\Groups;

class PurchaseOrderLineAddress
{
    #[Groups(['address'])]
    public string $name;
    #[Groups(['address'])]
    public ?string $addressLine1 = null;
    #[Groups(['address'])]
    public ?string $addressLine2 = null;
    #[Groups(['address'])]
    public ?string $addressLine3 = null;
    #[Groups(['address'])]
    public ?string $addressLine4 = null;
    #[Groups(['address'])]
    public ?string $addressLine5 = null;
    #[Groups(['address'])]
    public ?string $addressLine6 = null;
    #[Groups(['address'])]
    public string $postalCode;
    #[Groups(['address'])]
    public string $cityCode;
    #[Groups(['address'])]
    public string $cityDescription;
    #[Groups(['address'])]
    public string $stateOrProvinceCode;
    #[Groups(['address'])]
    public string $countryCode;
    #[Groups(['address'])]
    public ?string $telephone = null;
    #[Groups(['address'])]
    public ?string $fax = null;
    #[Groups(['address'])]
    public ?string $telex = null;
    #[Groups(['address'])]
    public ?string $internetURL = null;
    #[Groups(['address'])]
    public ?string $emailAddress = null;
    #[Groups(['address'])]
    public string $timeZone;
}
