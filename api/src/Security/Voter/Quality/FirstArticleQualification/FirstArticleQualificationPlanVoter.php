<?php

declare(strict_types=1);

namespace App\Security\Voter\Quality\FirstArticleQualification;

use App\Entity\Directory\People;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Manager\Directory\PeopleManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FirstArticleQualificationPlanVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'FEATURE_FAQ_PLAN_WRITE' === $attribute && $subject instanceof FirstArticleQualification;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof People) {
            return false;
        }

        $buyer = $subject->getBuyer();
        if (null !== $buyer && null !== $buyer->getSupervisor() && $user === $buyer->getSupervisor()) {
            return true;
        }

        if (!PeopleManager::hasGroup($user, 'ROLE_ENG')) {
            return false;
        }

        if ($subject->getPoster() === $user) {
            return true;
        }

        return $subject->getMembers()->contains($user);
    }
}
