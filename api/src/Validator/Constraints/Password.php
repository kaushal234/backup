<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Password extends Constraint
{
    public function __construct(
        public readonly string $startingWithSpacemessage = "A password can't start/finish with a space.",
        public readonly string $missingLettersMessage = 'Password must include at least one letter.',
        public readonly string $requireTwoOfThreeConstraints = 'Password must check 2 of these constraints: include both upper and lower case letters, include at least one number, include at least one special character.',
        public readonly string $no3CommonLettersWithName = 'Password must not contain 3 consecutive letters from the first or last name.',
        public readonly string $samePassword = 'Password must be different than the last 3 previous ones.',
        public readonly mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(
            options: $options,
            groups: $groups,
            payload: $payload,
        );
    }
}
