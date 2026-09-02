<?php

declare(strict_types=1);

namespace App\ION\Validator\Constraints\Sales;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class SalesOrder extends Constraint
{
    public string $message = 'The sales order {{ salesOrder }} does not exist.';

    public function validatedBy(): string
    {
        return SalesOrderValidator::class;
    }
}
