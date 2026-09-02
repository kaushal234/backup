<?php

declare(strict_types=1);

namespace App\Security\Voter\Support;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class EquipmentSerialEditVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof Location && 'EQUIPMENT_SERIAL_EDIT_VOTER' === $attribute;
    }

    /**
     * {@inheritdoc}
     *
     * @param Location $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $people = $token->getUser();
        if (!$people instanceof People) {
            return false;
        }

        $security = $this->getSecurity();

        if ($security->isGranted('FEATURE_EQUIPMENT_SERIAL_ADMIN')) {
            return true;
        }

        return $security->isGranted('EQUIPMENT_RECORD_EDIT_VOTER', $subject) || $security->isGranted('EQUIPMENT_RECORD_EDIT_FACTORY_VOTER', $subject);
    }
}
