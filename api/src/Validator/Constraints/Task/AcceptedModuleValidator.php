<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Task;

use App\Entity\ModuleName;
use App\Entity\Task\PartNumberTask;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AcceptedModuleValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AcceptedModule) {
            throw new UnexpectedTypeException($constraint, AcceptedModule::class);
        }

        if ($value instanceof PartNumberTask) {
            return;
        }
        /*
         * Accept validation if the module is in the list
         */
        if (null !== ModuleName::getClass($value->module->getName())) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ module }}', $value->module->getName())
            ->atPath('module')
            ->addViolation();
    }
}
