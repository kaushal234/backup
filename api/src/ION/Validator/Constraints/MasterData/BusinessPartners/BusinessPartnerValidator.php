<?php

declare(strict_types=1);

namespace App\ION\Validator\Constraints\MasterData\BusinessPartners;

use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class BusinessPartnerValidator extends ConstraintValidator
{
    private readonly BusinessPartnerManager $businessPartnerManager;

    public function __construct(BusinessPartnerManager $businessPartnerManager)
    {
        $this->businessPartnerManager = $businessPartnerManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AbstractBusinessPartner) {
            return;
        }

        if (null !== $value) {
            $businessPartnerType = $constraint->getBusinessPartnerType();
            $finder = 'find'.ucfirst($businessPartnerType);
            if (null === $this->businessPartnerManager->{$finder}((string) $value)) {
                $this->context->addViolation($constraint->message, ['{{ value }}' => $value, '{{ businessPartnerType }}' => $businessPartnerType]);
            }
        }
    }
}
