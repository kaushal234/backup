<?php

declare(strict_types=1);

namespace App\Security\Voter\Quality\Crab;

use App\Entity\Directory\People;
use App\Entity\Quality\Crab;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class CrabEditVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'FEATURE_CRAB_EDIT_VOTER' === $attribute && $subject instanceof Crab;
    }

    /**
     * @param Crab $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        return $this->getSecurity()->isGranted('FEATURE_CRAB_EDIT') || $user === $subject->createdBy;
    }
}
