<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service\TechnicianOnCall;

use App\Entity\Service\TechnicianOnCall;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HasSerialValidator extends ConstraintValidator
{
    /**
     * @param TechnicianOnCall           $value
     * @param CustomerServiceRecordExist $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null !== $value->equipmentRecord || null !== $value->serialNumber) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setTranslationDomain('technician_on_call')
            ->addViolation()
        ;
    }
}
