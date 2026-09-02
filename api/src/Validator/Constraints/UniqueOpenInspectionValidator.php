<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Sales\PreDeliveryInspection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueOpenInspectionValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof PreDeliveryInspection) {
            return;
        }

        $equipmentRecord = $value->getEquipmentRecord();
        if (null === $equipmentRecord) {
            return;
        }

        $openInspections = $equipmentRecord->getPreDeliveryInspections()->filter(static function (PreDeliveryInspection $inspection) use ($value) {
            return $inspection !== $value && $inspection->isOpen();
        });

        if (\count($openInspections) > 0) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
