<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class LockedValue extends Constraint
{
    public function __construct(
        public readonly string $value = '',
        public readonly ?string $propertyPath = null,
        public readonly string $errorMessage = "Value '{{ value }}' is locked and can't be updated.",
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
