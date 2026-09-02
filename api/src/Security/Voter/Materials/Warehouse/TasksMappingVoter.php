<?php

declare(strict_types=1);

namespace App\Security\Voter\Materials\Warehouse;

use App\Entity\Directory\People;
use App\Entity\Materials\Warehouse\TasksMapping;
use App\Manager\Directory\PeopleManager;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TasksMappingVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'TASKS_MAPPING_WRITE_VOTER' === $attribute;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        // The front might need to call the voter before having an instance to allow to show the ADD button
        if (null !== $subject && !($subject instanceof TasksMapping)) {
            return false;
        }

        if ($this->getSecurity()->isGranted('MOO_WHSE')) {
            return true;
        }

        return PeopleManager::hasOneOfGroups($user, ['ROLE_MLM', 'ROLE_WS'], null !== $subject ? $subject->getLocation() : null);
    }
}
