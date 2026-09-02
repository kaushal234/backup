<?php

declare(strict_types=1);

namespace App\Security\Voter\Specification;

use App\Entity\Directory\People;
use App\Entity\Module\Specification\UserStory;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class UserStoryWriteVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'FEATURE_USER_STORY_WRITE_VOTER' === $attribute && $subject instanceof UserStory;
    }

    /**
     * @param UserStory $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        $module = $subject->specification->module;

        return $this->getSecurity()->isGranted('FEATURE_USER_STORY_CREATE')
            || $this->getSecurity()->isGranted('FEATURE_USER_STORY_DELETE')
            || $module->getOperationalOwner() === $user
            || $module->getKeyUser() === $user
            || $module->getLocalKeyUsers()->contains($user);
    }
}
