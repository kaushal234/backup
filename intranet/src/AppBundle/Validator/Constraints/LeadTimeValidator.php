<?php

declare(strict_types=1);

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LeadTimeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof LeadTime) {
            throw new UnexpectedTypeException($constraint, LeadTime::class);
        }

        if (!\is_array($value)) {
            return;
        }

        foreach ($value as $familyId => $row) {
            if (0 === (int) $row['weeks'] && '' === (string) $row['description']) {
                continue;
            }

            if (0 !== (int) $row['weeks'] && '' !== (string) $row['description']) {
                continue;
            }

            if (0 === (int) $row['weeks']) {
                $this->context
                    ->buildViolation($constraint->missingWeekMessage)
                    ->setParameter('{{ family }}', $row['familyName'])
                    ->addViolation();
            }

            if ('' === (string) $row['description']) {
                $this->context
                    ->buildViolation($constraint->missingDescriptionMessage)
                    ->setParameter('{{ family }}', $row['familyName'])
                    ->addViolation();
            }
        }
    }
}
