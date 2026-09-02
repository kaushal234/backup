<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class OpenTasks extends Constraint
{
    public function __construct(
        public readonly array $statuses,
        public readonly string $module,
        public readonly string $message = 'Action not possible, you can\'t set status to {{ status }} as there are remaining open tasks on it.',
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
