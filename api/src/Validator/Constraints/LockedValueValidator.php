<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LockedValueValidator extends ConstraintValidator
{
    private readonly EntityManagerInterface $entityManager;

    private readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct(EntityManagerInterface $entityManager, PropertyAccessorInterface $propertyAccessor)
    {
        $this->entityManager = $entityManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof LockedValue) {
            return;
        }

        $uow = $this->entityManager->getUnitOfWork();
        $originalData = $uow->getOriginalEntityData($value);

        $newValue = $this->propertyAccessor->getValue($value, $constraint->propertyPath);
        $previousValue = $this->propertyAccessor->getValue($originalData, \sprintf('[%s]', $constraint->propertyPath));

        if ($newValue !== $constraint->value && $previousValue === $constraint->value) {
            $this->context->buildViolation($constraint->errorMessage)
                ->atPath($constraint->propertyPath)
                ->setParameter('{{ value }}', $constraint->value)
                ->addViolation();
        }
    }
}
