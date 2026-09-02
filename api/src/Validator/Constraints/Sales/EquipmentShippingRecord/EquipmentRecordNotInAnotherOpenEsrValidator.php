<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Sales\EquipmentShippingRecord;

use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EquipmentRecordNotInAnotherOpenEsrValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EquipmentRecordNotInAnotherOpenEsr) {
            throw new UnexpectedTypeException($constraint, EquipmentRecordNotInAnotherOpenEsr::class);
        }

        if (!$value instanceof EquipmentShippingRecordLine) {
            return;
        }

        $equipmentRecord = $value->equipmentRecord ?? null;
        $currentShippingRecord = $value->equipmentShippingRecord ?? null;

        if (null === $equipmentRecord || null === $currentShippingRecord) {
            return;
        }

        $conflictingEsrIds = [];

        /** @var EquipmentShippingRecordLine $shippingRecordLine */
        foreach ($equipmentRecord->getEquipmentShippingRecordLines() as $shippingRecordLine) {
            $shippingRecord = $shippingRecordLine->equipmentShippingRecord;

            if (EquipmentShippingRecord::CLOSED === $shippingRecord->getStatus()) {
                continue;
            }

            if ($shippingRecord === $currentShippingRecord) {
                continue;
            }

            $conflictingEsrIds[$shippingRecord->getId()] = $shippingRecord->getId();
        }

        if ([] === $conflictingEsrIds) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->setParameter('{{ serialNumber }}', $equipmentRecord->getSerialNumber())
            ->setParameter('{{ esrIds }}', implode(', ', $conflictingEsrIds))
            ->atPath('equipmentRecord')
            ->addViolation();
    }
}
