<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CreationInterventionValidator extends ConstraintValidator
{
    /**
     * @param Intervention         $value
     * @param CreationIntervention $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (AbstractCustomerServiceRecord::ASSIGNED === $value->customerServiceRecord->getStatus()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->addViolation()
        ;
    }
}
