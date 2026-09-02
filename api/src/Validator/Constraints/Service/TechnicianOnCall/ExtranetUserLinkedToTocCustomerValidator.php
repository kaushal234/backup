<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service\TechnicianOnCall;

use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\TechnicianOnCall;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ExtranetUserLinkedToTocCustomerValidator extends ConstraintValidator
{
    /**
     * @param ExtranetUser                    $value
     * @param ExtranetUserLinkedToTocCustomer $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ExtranetUserLinkedToTocCustomer) {
            throw new UnexpectedTypeException($constraint, ExtranetUserLinkedToTocCustomer::class);
        }

        if (!($parentObject = $this->context->getObject()) instanceof TechnicianOnCall) {
            throw new UnexpectedTypeException($parentObject, TechnicianOnCall::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value->getExtranetUserAcls()->exists(static fn ($key, $acl) => $parentObject->customer->getCrt()->contains($acl->getCrt()))) {
            $this->context->buildViolation($constraint->notAllowedMessage)
                ->setTranslationDomain('technician_on_call')
                ->setParameter('%extranetUser%', $value->getUsername())
                ->addViolation();
        }
    }
}
