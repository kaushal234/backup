<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service\TechnicianOnCall;

use App\Entity\Service\TechnicianOnCallType;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Entity\WarrantyClaim;
use LegacyBundle\Manager\WarrantyClaimManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HasWarrantyValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WarrantyClaimManager $warrantyClaimManager,
    ) {
    }

    /**
     * @param TechnicianOnCallType       $value
     * @param CustomerServiceRecordExist $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $this->context->getObject()->warrantyLegacyId) {
            return;
        }

        $uow = $this->entityManager->getUnitOfWork();
        $originalCustomerServiceRecord = $uow->getOriginalEntityData($this->context->getObject());

        if ([] === $originalCustomerServiceRecord) {
            return;
        }

        if ($value->getId() === $originalCustomerServiceRecord['technicianOnCallType']->getId() || TechnicianOnCallType::FACTORY === $this->context->getObject()->technicianOnCallType->name) {
            return;
        }

        $warrantyClaim = $this->warrantyClaimManager->findById($this->context->getObject()->warrantyLegacyId);

        if (!$warrantyClaim || WarrantyClaim::PENDING !== $warrantyClaim->status) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setTranslationDomain('technician_on_call')
            ->addViolation()
        ;
    }
}
