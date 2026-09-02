<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LeaderOnAssignedCustomerServiceRecordsValidator extends ConstraintValidator
{
    /**
     * @param AbstractCustomerServiceRecord          $value
     * @param LeaderOnAssignedCustomerServiceRecords $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null !== $value->getOpenIntervention()?->leader || AbstractCustomerServiceRecord::ASSIGNED !== $value->getStatus()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->atPath('status')
            ->addViolation()
        ;
    }
}
