<?php

declare(strict_types=1);

namespace App\Security\Voter\Manufacturing;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Manager\Directory\PeopleManager;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class EquipmentRecordEditVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Location && 'EQUIPMENT_RECORD_EDIT_VOTER' === $attribute;
    }

    /**
     * {@inheritdoc}
     *
     * @param Location $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $people = $token->getUser();
        if (!$people instanceof People) {
            return false;
        }

        $security = $this->getSecurity();

        if ($security->isGranted('FEATURE_EQUIPMENT_RECORD_EDIT')) {
            return true;
        }

        return PeopleManager::hasOneOfGroups($people, ['ROLE_COO', 'ROLE_PSM', 'ROLE_PM', 'PI_PM', 'ROLE_PLANNER'], $subject);
    }
}
