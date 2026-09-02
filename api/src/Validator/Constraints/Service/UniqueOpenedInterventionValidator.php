<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use App\Entity\Service\CustomerServiceRecord\Intervention;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueOpenedInterventionValidator extends ConstraintValidator
{
    /** @param ArrayCollection $value*/
    public function validate($value, Constraint $constraint): void
    {
        // todo refcato with entity methode
        $filteredInterventions = $value->filter(static function (Intervention $intervention) {
            return $intervention->isOpen();
        });

        if (1 < \count($filteredInterventions)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
