<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\ExtranetUser;

use App\Entity\Sales\ExtranetUserFavorite;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ExtranetUserFavoriteVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof ExtranetUserFavorite && 'FEATURE_EXTRANET_USER_FAVORITE' === $attribute;
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

        /* @var ExtranetUserFavorite $subject */
        return $user->getUserIdentifier() === $subject->getExtranetUser()->getUserIdentifier();
    }
}
