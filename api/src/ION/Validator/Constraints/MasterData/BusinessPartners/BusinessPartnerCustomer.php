<?php

declare(strict_types=1);

namespace App\ION\Validator\Constraints\MasterData\BusinessPartners;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class BusinessPartnerCustomer extends AbstractBusinessPartner
{
    public function getBusinessPartnerType(): string
    {
        return 'customer';
    }
}
