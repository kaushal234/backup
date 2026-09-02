<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class EquipmentShippingRecordLineLoad extends Constraint
{
    public function __construct(
        public readonly string $message = 'The factory {{ factory }} limit ({{ limit }}) has been reached for {{ date }}, already {{ count }} ESR line are booked and you try to add {{ value }}',
        public readonly ?string $errorPath = null,
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
