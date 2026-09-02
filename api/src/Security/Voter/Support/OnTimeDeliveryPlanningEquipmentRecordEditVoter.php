<?php

declare(strict_types=1);

namespace App\Security\Voter\Support;

use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class OnTimeDeliveryPlanningEquipmentRecordEditVoter extends AbstractVoter
{
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof EquipmentRecord && 'ODP_EQUIPMENT_RECORD_EDIT_VOTER' === $attribute;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $people = $token->getUser();
        if (!$people instanceof People) {
            return false;
        }

        $security = $this->getSecurity();

        return $security->isGranted('FEATURE_ODP_EDIT_ADMIN') || $security->isGranted('MOO_ER') || $security->isGranted('FEATURE_ODP_EDIT_SUPPORT') || $security->isGranted('FEATURE_ODP_EDIT_QUALITY');
    }
}
