<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Task;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AcceptedReferenceId extends Constraint
{
    public function __construct(
        public string $message = 'The reference ID "{{ referenceId }}" does not exist.',
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
