<?php

declare(strict_types=1);

namespace App\Security\Voter\MIS\Project;

use App\Entity\Directory\People;
use App\Entity\MIS\Project\Project;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ProjectFileDownloadVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'PROJECT_FILE_DOWNLOAD_VOTER' === $attribute && $subject instanceof Project;
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

        if (!$subject->isConfidential()) {
            return true;
        }

        return $this->getSecurity()->isGranted('FEATURE_MIS_PROJECT_FILE_DOWNLOAD')
            || $user === $subject->misOwner
            || $user === $subject->projectManager
        ;
    }
}
