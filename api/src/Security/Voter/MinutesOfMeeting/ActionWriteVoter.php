<?php

declare(strict_types=1);

namespace App\Security\Voter\MinutesOfMeeting;

use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Action;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ActionWriteVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'ACTION_WRITE_VOTER' === $attribute && $subject instanceof Action;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        return
            $user === ($assignee = $subject->getAssignee())
            || $user === ($supervisor = $assignee->getSupervisor())
            || (null !== $supervisor && $user === $supervisor->getSupervisor())
        ;
    }
}
