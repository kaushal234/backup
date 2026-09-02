<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SurveyCommissioningCustomerServiceRecordValidator extends ConstraintValidator
{
    /**
     * @param CommissioningCustomerServiceRecord       $value
     * @param SurveyCommissioningCustomerServiceRecord $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof CommissioningCustomerServiceRecord) {
            return;
        }

        if (AbstractCustomerServiceRecord::IN_PROGRESS === $value->getStatus()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->addViolation()
        ;
    }
}
