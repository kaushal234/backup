<?php

declare(strict_types=1);

namespace App\Security\Voter\Quality\NonConformity;

use App\Entity\Directory\People;
use App\Entity\Quality\NonConformity;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class NonConformityEditVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'NON_CONFORMITY_EDIT_VOTER' === $attribute && $subject instanceof NonConformity;
    }

    /**
     * {@inheritdoc}
     *
     * @param NonConformity $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        if (\in_array($subject->status, NonConformity::CLOSED_STATUSES, true)) {
            return $this->getSecurity()->isGranted('FEATURE_NON_CONFORMITY_EDIT_CLOSED');
        }

        return true;
    }
}
