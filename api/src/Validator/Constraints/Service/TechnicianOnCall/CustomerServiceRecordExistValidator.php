<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service\TechnicianOnCall;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CustomerServiceRecordExistValidator extends ConstraintValidator
{
    /**
     * @param Collection                 $value
     * @param CustomerServiceRecordExist $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value->isEmpty() || null === $this->context->getObject()->nestedCustomerServiceRecord) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation()
        ;
    }
}
