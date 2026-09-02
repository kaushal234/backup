<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service\TechnicianOnCall;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class HasWarranty extends Constraint
{
    public string $message = 'toc.messages.errors.warranty_open';
}
