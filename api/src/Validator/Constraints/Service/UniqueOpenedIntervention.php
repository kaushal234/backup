<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueOpenedIntervention extends Constraint
{
    public function __construct(
        public readonly string $message = 'An inter. already planned for this CSR',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }
}
