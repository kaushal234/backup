<?php

declare(strict_types=1);

namespace App\Security\Voter\Directory\People;

use App\Entity\Directory\People;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class PeopleMentoringAccessVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof People && 'PEOPLE_MENTORING_ACCESS_VOTER' === $attribute;
    }

    /**
     * @param People $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof People) {
            return false;
        }

        return $user === $subject
            || $user === $subject->getMentor()
            || $this->getSecurity()->isGranted('FEATURE_PEOPLE_MENTORING_VIEW')
            || $this->getSecurity()->isGranted('FEATURE_PEOPLE_UPDATE_VOTER', $subject);
    }
}
