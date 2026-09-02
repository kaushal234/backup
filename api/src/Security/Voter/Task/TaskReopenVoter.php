<?php

declare(strict_types=1);

namespace App\Security\Voter\Task;

use App\Entity\Directory\People;
use App\Entity\Task\Task;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class TaskReopenVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'TASK_REOPEN_VOTER' === $attribute && $subject instanceof Task;
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

        return Task::CLOSED === $subject->getStatus() && $this->getSecurity()->isGranted('TASK_TRANSFER_VOTER', $subject) && $subject->closedAt > (new \DateTime('60 days ago'));
    }
}
