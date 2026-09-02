<?php

declare(strict_types=1);

namespace App\Validator\Constraints\AI;

use App\Entity\AI\AILog;
use App\Repository\AI\AILogRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MaxPinnedAILogsValidator extends ConstraintValidator
{
    public function __construct(private readonly AILogRepository $repository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof AILog || !$constraint instanceof MaxPinnedAILogs) {
            return;
        }

        if (!$value->pinned) {
            return;
        }

        $count = $this->repository->countPinnedByPeople($value->people, $value->getId());

        if ($count >= $constraint->max) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ max }}', (string) $constraint->max)
                ->addViolation();
        }
    }
}
