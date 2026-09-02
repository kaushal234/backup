<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use LegacyBundle\Manager\TaskManager;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OpenTasksValidator extends ConstraintValidator
{
    private readonly TaskManager $taskManager;
    private readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct(TaskManager $taskManager, PropertyAccessorInterface $propertyAccessor)
    {
        $this->taskManager = $taskManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof OpenTasks) {
            return;
        }

        $status = $this->propertyAccessor->getValue($value, 'status');
        if (\in_array($status, $constraint->statuses, true) && !empty($this->taskManager->findOpenTasksByModule($constraint->module, $this->propertyAccessor->getValue($value, 'id')))) {
            $this->context->buildViolation($constraint->message)
               ->setParameter('{{ status }}', $status)
               ->addViolation();
        }
    }
}
