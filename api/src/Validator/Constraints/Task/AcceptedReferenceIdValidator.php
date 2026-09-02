<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Task;

use App\Entity\ModuleName;
use App\Entity\Task\PartNumberTask;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AcceptedReferenceIdValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AcceptedReferenceId) {
            throw new UnexpectedTypeException($constraint, AcceptedReferenceId::class);
        }

        if ($value instanceof PartNumberTask) {
            return;
        }

        $className = ModuleName::getClass($value->module->getName());
        $id = $value->referenceId;
        if (isset($className)) {
            $object = $this->entityManager->getRepository($className)->find($id);
        }

        if (isset($object)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ referenceId }}', (string) $id)
            ->atPath('referenceId')
            ->addViolation();
    }
}
