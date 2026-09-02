<?php

declare(strict_types=1);

namespace App\Doctrine\Constraints\Survey;

use App\Entity\Survey\Answer;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AnswerValueInRatingTypeValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): bool
    {
        if (!$value instanceof Answer) {
            throw new \InvalidArgumentException(\sprintf('Constraint %s not properly used. Should be used on class %s ', $value::class, Answer::class));
        }

        $answerValue = $value->getValue();
        $ratingType = $value->getRatingType();

        if ($ratingType->getMin() <= $answerValue && $answerValue <= $ratingType->getMax()) {
            return true;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', (string) $answerValue)
            ->setParameter('{{ rating_min }}', (string) $ratingType->getMin())
            ->setParameter('{{ rating_max }}', (string) $ratingType->getMax())
            ->addViolation();

        return false;
    }
}
