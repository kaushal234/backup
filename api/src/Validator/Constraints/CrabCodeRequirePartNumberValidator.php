<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Dto\Quality\CrabSalesOrderLine;
use App\Entity\Quality\Crab;
use App\Entity\Quality\CrabCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CrabCodeRequirePartNumberValidator extends ConstraintValidator
{
    /**
     * @param CrabCodeRequirePartNumber $constraint
     * @param Crab|CrabSalesOrderLine   $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (CrabCode::FAI !== $value->code?->description) {
            return;
        }

        $part = $value instanceof CrabSalesOrderLine ? $value->partNumber : $value->getPart();

        if (empty($part)) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain('crab')
                ->setParameter('{{ code }}', (string) $value->code->code)
                ->setParameter('{{ descriptionCode }}', $value->code->description)
                ->atPath('code')
                ->addViolation();
        }
    }
}
