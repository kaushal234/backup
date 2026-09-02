<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CreationIntervention extends Constraint
{
    public function __construct(
        public readonly string $message = 'An intervention cannot be created without the correct status on Customer Service Record',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
