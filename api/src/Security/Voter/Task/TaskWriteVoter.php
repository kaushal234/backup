<?php

declare(strict_types=1);

namespace App\Security\Voter\Task;

use App\Entity\BaseTask;
use App\Entity\Directory\People;
use App\Entity\Task\Task;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class TaskWriteVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'TASK_WRITE_VOTER' === $attribute && $subject instanceof Task;
    }

    /**
     * @param Task $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        return (\in_array($subject->getStatus(), [Task::PENDING, BaseTask::IN_PROGRESS], true)
                && ($subject->createdBy === $user || $subject->createdBy?->getSupervisor() === $user
                    || $subject->assignee === $user || $subject->assignee?->getSupervisor() === $user))
            || $this->getSecurity()->isGranted('FEATURE_TASK_WRITE');
    }
}
