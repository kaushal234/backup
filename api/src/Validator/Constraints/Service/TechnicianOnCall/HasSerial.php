<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service\TechnicianOnCall;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class HasSerial extends Constraint
{
    public string $message = 'toc.messages.errors.serials';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
