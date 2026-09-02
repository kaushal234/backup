<?php

declare(strict_types=1);

namespace App\ION\Validator\Constraints\MasterData\BusinessPartners;

use Symfony\Component\Validator\Constraint;

abstract class AbstractBusinessPartner extends Constraint
{
    public string $message = 'The {{ businessPartnerType }} {{ value }} does not exist.';

    abstract public function getBusinessPartnerType(): string;

    public function validatedBy(): string
    {
        return BusinessPartnerValidator::class;
    }
}
