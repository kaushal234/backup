<?php

declare(strict_types=1);

namespace App\Security\Voter\MIS\Project;

use App\Entity\Directory\People;
use App\Entity\MIS\Project\Project;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ProjectCommentVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'PROJECT_COMMENT_VOTER' === $attribute && $subject instanceof Project;
    }

    /**
     * {@inheritdoc}
     *
     * @param Project $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        return !$subject->isConfidential()
            || (
                $this->getSecurity()->isGranted('FEATURE_MIS_PROJECT_CIO_EDIT')
                    || $user === $subject->projectManager
                    || $user === $subject->misOwner
                    || $subject->getMisMembers()->contains($user)
                    || $subject->getModuleKeyUsers()->contains($user)
            )
        ;
    }
}
