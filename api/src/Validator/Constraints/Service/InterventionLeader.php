<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class InterventionLeader extends Constraint
{
    public function __construct(
        public readonly string $message = 'An AST, a CSTL, a CSM or a CSS position is mandatory to be leader on the intervention',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }
}
