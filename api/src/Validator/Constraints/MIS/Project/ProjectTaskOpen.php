<?php

declare(strict_types=1);

namespace App\Validator\Constraints\MIS\Project;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ProjectTaskOpen extends Constraint
{
    public function __construct(
        public readonly string $message = 'At least one task of previous Phase is still open, please close it before changing status.',
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
