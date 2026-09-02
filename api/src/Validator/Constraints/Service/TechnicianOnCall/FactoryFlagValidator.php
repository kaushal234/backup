<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service\TechnicianOnCall;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FactoryFlagValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    /**
     * @param bool        $value
     * @param FactoryFlag $constraint
     *
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        $uow = $this->entityManager->getUnitOfWork();
        $originalCustomerServiceRecord = $uow->getOriginalEntityData($this->context->getObject());

        if ([] === $originalCustomerServiceRecord || $originalCustomerServiceRecord['factoryFlag'] === $value) {
            return;
        }

        if ($value && !$this->security->isGranted('FEATURE_TECHNICIAN_ON_CALL_OPEN_FACTORY_FLAG')) {
            $this->context
                ->buildViolation('toc.messages.security.factory_flag_open')
                ->setTranslationDomain('technician_on_call')
                ->addViolation()
            ;
        }

        if (!$value && !$this->security->isGranted('FEATURE_TECHNICIAN_ON_CALL_CLOSE_FACTORY_FLAG')) {
            $this->context
                ->buildViolation('toc.messages.security.factory_flag_close')
                ->setTranslationDomain('technician_on_call')
                ->addViolation()
            ;
        }
    }
}
