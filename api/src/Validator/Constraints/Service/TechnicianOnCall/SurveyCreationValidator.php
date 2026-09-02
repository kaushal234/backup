<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service\TechnicianOnCall;

use App\Entity\Service\TechnicianOnCall\TechnicianOnCallSurvey;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SurveyCreationValidator extends ConstraintValidator
{
    /**
     * @param TechnicianOnCallSurvey     $value
     * @param CustomerServiceRecordExist $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value->technicianOnCall->getIsOpen()) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setTranslationDomain('technician_on_call')
            ->addViolation()
        ;
    }
}
