<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class TimeZone extends Constraint
{
    public function __construct(
        public readonly string $message = 'The time zone "%string%" is invalid.',
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
}
