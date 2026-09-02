<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Sales\EquipmentShippingRecord;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class EquipmentRecordMatchesCustomer extends Constraint
{
    public string $message = '<br>Serial number {{ serialNumber }} cannot be assigned to customer {{ customerName }} because this customer is neither the Buyer nor the End User of this Equipment Record.<br>';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
