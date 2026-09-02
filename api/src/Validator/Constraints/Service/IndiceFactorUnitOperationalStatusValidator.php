<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use App\Entity\IndiceFactor as EnumIndiceFactor;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Support\UnitOperationalStatus;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IndiceFactorUnitOperationalStatusValidator extends ConstraintValidator
{
    /**
     * @param TechnicianOnCall                  $value
     * @param IndiceFactorUnitOperationalStatus $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (EnumIndiceFactor::IF_1->value !== $value->indiceFactor || null === $value->unitOperationalStatus || UnitOperationalStatus::MCF === $value->unitOperationalStatus->getName()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->setTranslationDomain('technician_on_call')
            ->atPath('indiceFactor')
            ->addViolation()
        ;
    }
}
