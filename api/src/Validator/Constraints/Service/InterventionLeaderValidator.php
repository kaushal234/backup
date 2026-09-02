<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use App\Entity\Directory\People;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InterventionLeaderValidator extends ConstraintValidator
{
    /**
     * @param People             $value
     * @param InterventionLeader $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        $positionCode = $value->getPosition()?->getCode();
        $availableLeaderPositionCode = ['AST', 'CSTL', 'CSM', 'CSS', 'ENG'];

        if (\in_array($positionCode, $availableLeaderPositionCode, true)) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->addViolation()
        ;
    }
}
