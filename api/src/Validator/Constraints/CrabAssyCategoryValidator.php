<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Dto\Quality\CrabSalesOrderLine;
use App\Entity\EquipmentRecord;
use App\Entity\Quality\Crab;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CrabAssyCategoryValidator extends ConstraintValidator
{
    /**
     * @param CrabAssyCategory        $constraint
     * @param Crab|CrabSalesOrderLine $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (Crab::ASSY !== $value->category) {
            return;
        }

        $equipmentRecord = $value->equipmentRecord;
        if (!$value->equipmentRecord instanceof EquipmentRecord) {
            return;
        }

        if (null !== $equipmentRecord->getFirstGreenTagDate() || $equipmentRecord->getYellowTagDate()) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain('crab')
                ->atPath('category')
                ->addViolation();
        }
    }
}
