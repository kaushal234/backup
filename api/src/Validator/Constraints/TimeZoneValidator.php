<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TimeZoneValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!\in_array($value, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL, 'EN'), true) && !empty($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}
