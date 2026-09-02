<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Support\EquipmentRecord\HourMeterTransaction\HourMeterTransaction as HourMeterTransactionObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HourMeterTransactionValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof HourMeterTransaction || !$value instanceof HourMeterTransactionObject) {
            return;
        }

        if ($value->hourMeter >= $value->getEquipmentRecord()->getHourMeter()) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->atPath('hourMeter')
            ->addViolation();
    }
}
