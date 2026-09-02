<?php

declare(strict_types=1);

namespace App\Validator\Constraints\AI;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MaxPinnedAILogs extends Constraint
{
    public int $max = 15;

    public string $message = 'You cannot pin more than {{ max }} conversations.';

    public function __construct(
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
