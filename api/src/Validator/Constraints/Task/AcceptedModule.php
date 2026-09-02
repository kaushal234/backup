<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Task;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AcceptedModule extends Constraint
{
    public function __construct(
        public readonly string $message = 'An inter. already planned for this CSR',
        ?array $groups = null,
        $payload = null
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
