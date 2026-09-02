<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class OperatorOnOpenCustomerServiceRecords extends Constraint
{
    public function __construct(
        public readonly string $message = 'You can\'t update operators on a closed Intervention',
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
