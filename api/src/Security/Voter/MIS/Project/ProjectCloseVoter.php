<?php

declare(strict_types=1);

namespace App\Security\Voter\MIS\Project;

use App\Entity\Directory\People;
use App\Entity\MIS\Project\Project;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ProjectCloseVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'PROJECT_CLOSE_VOTER' === $attribute && $subject instanceof Project;
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

        $security = $this->getSecurity();

        return (\in_array($subject->indicesFactor, [Project::IF1, Project::IF10, Project::IF100], true) && ($user === $subject->projectManager || $user === $subject->misOwner))
            || (\in_array($subject->indicesFactor, [Project::IF1000, Project::IF10000], true) && $security->isGranted('FEATURE_MIS_PROJECT_CLOSE'));
    }
}
