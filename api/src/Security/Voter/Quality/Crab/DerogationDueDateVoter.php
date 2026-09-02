<?php

declare(strict_types=1);

namespace App\Security\Voter\Quality\Crab;

use App\Entity\Directory\People;
use App\Entity\Quality\Derogation;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class DerogationDueDateVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'FEATURE_DEROGATION_DUE_DATE_ADMIN_VOTER' === $attribute && $subject instanceof Derogation;
    }

    /**
     * @param Derogation $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        return $this->getSecurity()->isGranted('FEATURE_DEROGATION_DUE_DATE_ADMIN') || $user === $subject->assignee;
    }
}
