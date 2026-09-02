<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Sales\EquipmentShippingRecord;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class EquipmentRecordNotInAnotherOpenEsr extends Constraint
{
    public string $message = '<br> Serial number {{ serialNumber }} already belongs to another open Equipment Shipping Record: {{ esrIds }}. This Equipment Record must be removed from one of the open ESR before saving. <br>';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
