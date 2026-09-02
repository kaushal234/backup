<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class CrabAssyCategory extends Constraint
{
    public function __construct(
        public readonly ?string $message = 'crab.errors.assy_category_error',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
