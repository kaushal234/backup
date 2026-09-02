<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Sales\ExtranetUser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidExtranetUserValidator extends ConstraintValidator
{
    /**
     * @param ExtranetUser $value
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidExtranetUser) {
            throw new UnexpectedTypeException($constraint, ValidExtranetUser::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof ExtranetUser) {
            throw new UnexpectedValueException($value, ExtranetUser::class);
        }

        if ($value->getExtranetUserProfile()->archived) {
            $this->context->buildViolation($constraint->disabledMessage)
                ->setTranslationDomain('technician_on_call')
                ->setParameter('%extranetUser%', $value->getUsername())
                ->addViolation();
        }
    }
}
