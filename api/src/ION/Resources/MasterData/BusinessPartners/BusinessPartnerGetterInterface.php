<?php

declare(strict_types=1);

namespace App\ION\Resources\MasterData\BusinessPartners;

interface BusinessPartnerGetterInterface
{
    public function getBusinessPartnerCode(): ?string;
}
