<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class HourMeterTransaction extends Constraint
{
    public function __construct(
        public readonly mixed $message = 'The new hour meter should be equal or greater than the actual one.',
        public readonly mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(
            options: $options,
            groups: $groups,
            payload: $payload,
        );
    }

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
