<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Directory\People;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HasGroupValidator extends ConstraintValidator
{
    /**
     * @param People   $value
     * @param HasGroup $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$value instanceof People) {
            throw new \InvalidArgumentException('The value must be an instance of People');
        }

        foreach ($value->getAcls() as $acl) {
            // If a group match with one of the roles, validation is passed
            if (\in_array($acl->getGroup()->getName(), $constraint->roles, true)) {
                return;
            }
        }

        $this->context
            ->buildViolation($constraint->message)
            ->addViolation()
        ;
    }
}
