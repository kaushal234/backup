<?php

declare(strict_types=1);

namespace App\Validator\Constraints\MIS\Project;

use App\Entity\MIS\Project\Phase;
use App\Entity\MIS\Project\Project;
use App\Entity\Task\Task;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ProjectTaskOpenValidator extends ConstraintValidator
{
    /**
     * @param Project         $value
     * @param ProjectTaskOpen $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        $activePhase = $value->getActivePhase();
        if (null === $activePhase && \in_array($value->getStatus(), [Project::PENDING, Project::CANCELLED], true)) {
            return;
        }

        $openTask = false;
        // Active phase is null means we are closing project
        if (null === $activePhase) {
            /** @var Phase $phase4 */
            $phase4 = $value->getPhases()->filter(static fn (Phase $phase) => 4 === $phase->number)->first();
            foreach ($phase4->getTasks() as $task) {
                if (Task::CLOSED !== $task->getStatus()) {
                    $openTask = true;
                }
            }
        } else {
            foreach ($value->getPhases() as $phase) {
                if ($openTask) {
                    continue;
                }
                if ($phase->number < $activePhase->number) {
                    foreach ($phase->getTasks() as $task) {
                        if (Task::CLOSED !== $task->getStatus()) {
                            $openTask = true;
                        }
                    }
                }
            }
        }

        if (!$openTask) {
            return;
        }

        $this->context->buildViolation($constraint->message)->atPath('status')->addViolation();
    }
}
