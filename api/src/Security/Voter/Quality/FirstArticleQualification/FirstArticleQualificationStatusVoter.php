<?php

declare(strict_types=1);

namespace App\Security\Voter\Quality\FirstArticleQualification;

use App\Entity\Directory\People;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Manager\Directory\PeopleManager;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class FirstArticleQualificationStatusVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'FAQ_STATUS_VOTER' === $attribute && $subject instanceof FirstArticleQualification;
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

        if ($this->getSecurity()->isGranted('FEATURE_FAQ_PLAN_WRITE')) {
            return true;
        }

        if ($this->getSecurity()->isGranted('FEATURE_FIRST_ARTICLE_QUALIFICATION_STATUS_OVERRIDE')
            || $this->getSecurity()->isGranted('MOO_FAQ')) {
            return true;
        }

        return
            \in_array($subject->getIFactor(), ['IF1', 'IF10'], true)
            && PeopleManager::hasGroup($user, 'ROLE_PSE')
        ;
    }
}
