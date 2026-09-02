<?php

declare(strict_types=1);

namespace App\ION\Resources\Procurement;

use Symfony\Component\Serializer\Attribute\Groups;

class RequestForQuotationBidder
{
    #[Groups(['request_for_quotation'])]
    public string $bidderCode;

    #[Groups(['request_for_quotation'])]
    public string $name;
}
