<?php

declare(strict_types=1);

namespace App\SageParts\P21\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class SagePartsSupplier extends Constraint
{
    public string $message = 'The supplier {{ value }} does not exist.';
}
