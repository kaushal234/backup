<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service\TechnicianOnCall;

use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\TechnicianOnCall;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ExtranetUserLinkedToEquipmentRecordValidator extends ConstraintValidator
{
    /**
     * @param ExtranetUser                        $value
     * @param ExtranetUserLinkedToEquipmentRecord $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ExtranetUserLinkedToEquipmentRecord) {
            throw new UnexpectedTypeException($constraint, ExtranetUserLinkedToEquipmentRecord::class);
        }

        if (!($parentObject = $this->context->getObject()) instanceof TechnicianOnCall) {
            throw new UnexpectedTypeException($parentObject, TechnicianOnCall::class);
        }

        if (null === $value) {
            return;
        }

        $equipmentRecord = $parentObject->equipmentRecord;

        $customers = array_values(array_unique(array_filter([
            $equipmentRecord?->getEndUser(),
            $equipmentRecord?->getBuyer(),
            $equipmentRecord?->getMaintainer(),
            $parentObject->customer,
        ]), \SORT_REGULAR));

        $allowedCrts = array_values(array_unique(
            array_reduce($customers, static fn (array $carry, $customer) => [...$carry, ...$customer->getCrt()->toArray()], []),
            \SORT_REGULAR
        ));

        if (0 === \count(array_filter($value->getExtranetUserAcls()->toArray(), static fn ($acl) => \in_array($acl->getCrt(), $allowedCrts, true)))) {
            $this->context->buildViolation($constraint->notAllowedMessage)
                ->setTranslationDomain('technician_on_call')
                ->setParameter('%extranetUser%', $value->getUsername())
                ->addViolation();
        }
    }
}
