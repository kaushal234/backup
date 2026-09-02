<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use App\Entity\Service\CustomerServiceRecord\Intervention;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OperatorOnOpenCustomerServiceRecordsValidator extends ConstraintValidator
{
    /**
     * @param Intervention                         $value
     * @param OperatorOnOpenCustomerServiceRecords $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value->getOperators() instanceof PersistentCollection) {
            return;
        }

        if ($value->isOpen()) {
            return;
        }

        if ([] === $value->getOperators()->getInsertDiff() && [] === $value->getOperators()->getDeleteDiff()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->addViolation()
        ;
    }
}
