<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\ExtranetUser;

use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserProfile;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ExtranetUserEditionVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return ($subject instanceof ExtranetUser || $subject instanceof ExtranetUserProfile) && 'FEATURE_EXTRANET_USER_EDIT' === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($subject instanceof ExtranetUser) {
            return $user->getUserIdentifier() === $subject->getUserIdentifier();
        }
        if ($subject instanceof ExtranetUserProfile) {
            return null !== $subject->extranetUser && $user->getUserIdentifier() === $subject->extranetUser->getUserIdentifier();
        }

        return false;
    }
}
