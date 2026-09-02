<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Support\EquipmentSerial;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotNullEquipmentSerialComponentValidator extends ConstraintValidator
{
    private static array $processedEquipmentRecordIds = [];

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotNullEquipmentSerialComponent) {
            return;
        }

        if (null !== $value) {
            return;
        }

        /** @var EquipmentSerial $equipmentSerial */
        $equipmentSerial = $this->context->getObject();
        $equipmentRecordId = $equipmentSerial->equipmentRecord->getId();
        if (\in_array($equipmentRecordId, self::$processedEquipmentRecordIds, true)) {
            return;
        }
        self::$processedEquipmentRecordIds[] = $equipmentRecordId;
        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ equipmentRecordId }}', (string) $equipmentRecordId)
            ->addViolation();
    }
}
