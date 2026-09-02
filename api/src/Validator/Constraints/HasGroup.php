<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class HasGroup extends Constraint
{
    public string $message = 'people.has_role';
    public array $roles;

    #[HasNamedArguments]
    public function __construct(array $roles, mixed $options = null, ?array $groups = null, mixed $payload = null)
    {
        $this->roles = $roles;
        parent::__construct($options, $groups, $payload);
    }
}
