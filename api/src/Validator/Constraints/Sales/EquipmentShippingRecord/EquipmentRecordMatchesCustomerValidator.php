<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Sales\EquipmentShippingRecord;

use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EquipmentRecordMatchesCustomerValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EquipmentRecordMatchesCustomer) {
            throw new UnexpectedTypeException($constraint, EquipmentRecordMatchesCustomer::class);
        }

        if (!$value instanceof EquipmentShippingRecordLine) {
            return;
        }

        $equipmentRecord = $value->equipmentRecord ?? null;
        $equipmentShippingRecord = $value->equipmentShippingRecord ?? null;
        $customer = $equipmentShippingRecord->customer ?? null;

        if (null === $equipmentRecord || null === $customer) {
            return;
        }

        if (
            !$customer->isEquipmentRecordUser($equipmentRecord)
            && !$customer->isEquipmentRecordBuyer($equipmentRecord)
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ serialNumber }}', (string) $equipmentRecord->getSerialNumber())
                ->setParameter('{{ customerName }}', (string) $customer->getName())
                ->atPath('equipmentRecord')
                ->addViolation();
        }
    }
}
