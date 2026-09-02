<?php

declare(strict_types=1);

namespace App\Security\Voter\Directory\People;

use App\Entity\Directory\People;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class PeopleAddAclVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'FEATURE_PEOPLE_ADD_ACL_VOTER' === $attribute && $subject instanceof People;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        $security = $this->getSecurity();

        /** @var People|null $supervisor */
        $supervisor = $subject->getSupervisor();

        return (null !== $supervisor && $user === $supervisor)
            || (null !== $supervisor && null !== $supervisor->getSupervisor() && $user === $supervisor->getSupervisor())
            || $security->isGranted('FEATURE_ACL_WRITE');
    }
}
