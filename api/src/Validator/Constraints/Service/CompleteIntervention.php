<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use App\Entity\Service\CustomerServiceRecord\Intervention;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CompleteIntervention extends Constraint
{
    public function __construct(
        public readonly array $messages = [
            Intervention::SOLVED => 'Intervention cannot be solved without CSR in progress',
            Intervention::TO_CONTINUE => 'Intervention cannot be to continue without CSR in progress',
            'end_date' => 'Ended date is required to close the intervention',
        ],
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
