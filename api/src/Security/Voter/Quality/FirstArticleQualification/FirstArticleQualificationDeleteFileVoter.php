<?php

declare(strict_types=1);

namespace App\Security\Voter\Quality\FirstArticleQualification;

use App\Entity\Directory\People;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class FirstArticleQualificationDeleteFileVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'FAQ_DELETE_FILE_VOTER' === $attribute && $subject instanceof FirstArticleQualification;
    }

    /**
     * @param FirstArticleQualification $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        if ($this->getSecurity()->isGranted('FEATURE_FAQ_DELETE_FILE')) {
            return true;
        }

        return $user === $subject->getOwner()
            || $user === $subject->getPoster()
            || $user === $subject->getBuyer()
            || $subject->getMembers()->contains($user)
        ;
    }
}
