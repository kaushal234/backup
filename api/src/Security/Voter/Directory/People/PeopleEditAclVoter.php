<?php

declare(strict_types=1);

namespace App\Security\Voter\Directory\People;

use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class PeopleEditAclVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'FEATURE_PEOPLE_ACL_VOTER' === $attribute && ($subject instanceof Acl || $subject instanceof People);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        $security = $this->getSecurity();

        if ($subject instanceof Acl) {
            /** @var People $people */
            $people = $subject->getUser();

            return $user === $subject->getUser() || (null !== $people->getSupervisor() && $user === $people->getSupervisor())
                || $security->isGranted('FEATURE_ACL_WRITE');
        }

        return $user === $subject || (null !== $subject->getSupervisor() && $user === $subject->getSupervisor())
            || $security->isGranted('FEATURE_ACL_WRITE');
    }
}
