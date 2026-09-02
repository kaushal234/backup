<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class SurveyCommissioningCustomerServiceRecord extends Constraint
{
    public function __construct(
        public readonly string $message = 'A survey cannot be answered without the correct status on Customer Service Record',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }
}
