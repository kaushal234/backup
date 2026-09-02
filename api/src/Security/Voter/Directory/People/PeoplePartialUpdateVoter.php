<?php

declare(strict_types=1);

namespace App\Security\Voter\Directory\People;

use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Manager\Directory\PeopleManager;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class PeoplePartialUpdateVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof People && 'PEOPLE_PARTIAL_UPDATE_VOTER' === $attribute;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$subject instanceof People || !$user instanceof People) {
            return false;
        }

        if (PeopleManager::hasOneOfGroups($user, ['SUPERUSER', 'GG_MIS'])) {
            return true;
        }

        if (!$subject
            ->getAcls()
            ->filter(static fn (Acl $acl) => 'ACL_AUTH_INTRANET' === $acl->getGroup()->getName())
            ->isEmpty()) {
            return false;
        }

        if ($subject->isDisabled() || $subject->isHidden()) {
            return false;
        }

        if ($user === ($supervisor = $subject->getSupervisor())) {
            return true;
        }

        if (null !== $supervisor && $user === $supervisor->getSupervisor()) {
            return true;
        }

        if (null === $businessUnit = $user->getBusinessUnit()) {
            return false;
        }

        $security = $this->getSecurity();

        return $security->isGranted('role_MPE', $businessUnit->getLocation())
            || $security->isGranted('ROLE_PS', $businessUnit->getLocation())
            || PeopleManager::hasGroup($user, 'GG_HR', $businessUnit->getLocation())
        ;
    }
}
