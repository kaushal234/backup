<?php

declare(strict_types=1);

namespace App\Security\Voter\MIS\GuestUser;

use App\Entity\Directory\People;
use App\Entity\MIS\GuestUser\GuestUser;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class GuestUserUpdateVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'GUEST_USER_UPDATE_VOTER' === $attribute && $subject instanceof GuestUser;
    }

    /**
     * @param GuestUser $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        return $this->getSecurity()->isGranted('FEATURE_GUEST_USER_UPDATE') || $subject->getSupervisor() === $user;
    }
}
