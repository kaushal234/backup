<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CompleteInterventionValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param Intervention         $value
     * @param CompleteIntervention $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value->isCompleted()) {
            return;
        }

        if (null === $value->endedAt) {
            $this->context
                ->buildViolation($constraint->messages['end_date'])
                ->atPath('status')
                ->addViolation()
            ;
        }

        $customerServiceRecord = $value->customerServiceRecord;

        $uow = $this->entityManager->getUnitOfWork();
        $originalCustomerServiceRecord = $uow->getOriginalEntityData($customerServiceRecord);

        if ($value->customerServiceRecord->isClosed() || $customerServiceRecord->isClosed($originalCustomerServiceRecord['status'])) {
            return;
        }

        $customerServiceRecordInProgressStatus = [
            AbstractCustomerServiceRecord::PENDING,
            AbstractCustomerServiceRecord::PLANNED,
        ];
        if (\in_array($value->customerServiceRecord->getStatus(), $customerServiceRecordInProgressStatus, true)) {
            $this->context
                ->buildViolation($constraint->messages[$value->getStatus()])
                ->atPath('status')
                ->addViolation()
            ;
        }
    }
}
