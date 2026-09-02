<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\Items\ItemClassification;

use Symfony\Component\Serializer\Attribute\Groups;

class Reference
{
    #[Groups(['item'])]
    public string $businessPartner;

    #[Groups(['item'])]
    public string $businessPartnerName;

    #[Groups(['item'])]
    public string $partNumber;

    #[Groups(['item'])]
    public string $itemDescription;
}
