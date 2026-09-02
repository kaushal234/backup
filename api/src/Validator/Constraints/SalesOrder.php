<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class SalesOrder extends Constraint
{
    public function __construct(
        public readonly string $messageInvalidJuridicalLocation = 'This value is not a valid juridical location for the SSO {{ name }}.',
        public readonly string $messageInforLnCustomer = 'This value should not be null for the SSO {{ name }}.',
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
