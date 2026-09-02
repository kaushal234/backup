<?php

declare(strict_types=1);

namespace App\Security\Voter\Task;

use App\Entity\BaseTask;
use App\Entity\Directory\People;
use App\Entity\Task\RenewGuestUser;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class RenewGuestUserVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'RENEW_GUEST_USER_VOTER' === $attribute && $subject instanceof RenewGuestUser;
    }

    /**
     * @param RenewGuestUser $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        return (\in_array($subject->getStatus(),
            [BaseTask::PENDING, BaseTask::IN_PROGRESS], true)
            && $subject->assignee === $user)
            || $this->getSecurity()->isGranted('FEATURE_TASK_WRITE');
    }
}
