<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service\TechnicianOnCall;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class CustomerServiceRecordExist extends Constraint
{
    public string $message = 'technician_on_call.customer_service_record.already_exist';
}
