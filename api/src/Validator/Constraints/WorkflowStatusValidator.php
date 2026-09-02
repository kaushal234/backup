<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Workflow\Exception\LogicException;

class WorkflowStatusValidator extends ConstraintValidator
{
    public function __construct(
        public readonly EntityManagerInterface $entityManager,
        public readonly WorkflowStatusUpdater $workflowStatusUpdater,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();

        if (!$originalEntityData = $unitOfWork->getOriginalEntityData($value)) {
            return;
        }
        $oldStatus = $originalEntityData['status'];

        if ($oldStatus !== $value->getStatus()) {
            $fakeObject = new ($value::class);
            $fakeObject->setStatus($oldStatus);

            try {
                $this->workflowStatusUpdater->applyStatus($fakeObject, $value->getStatus());
            } catch (LogicException $exception) {
                $this->context->buildViolation($exception->getMessage())
                    ->addViolation();
            }
        }
    }
}
