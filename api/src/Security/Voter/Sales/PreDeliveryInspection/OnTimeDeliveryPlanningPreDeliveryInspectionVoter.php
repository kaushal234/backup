<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\PreDeliveryInspection;

use App\Entity\AbstractInspection;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Manager\Directory\PeopleManager;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class OnTimeDeliveryPlanningPreDeliveryInspectionVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'ODP_PDI_VOTER' === $attribute && $subject instanceof EquipmentRecord;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        if (!$subject instanceof EquipmentRecord || AbstractInspection::SUCCESSFUL === $subject->getLastPreDeliveryInspection()?->getStatus()) {
            return false;
        }

        if (null === $subject->getBuyer()) {
            return false;
        }

        $security = $this->getSecurity();

        return
            $security->isGranted('FEATURE_ODP_EDIT_ADMIN')
            || $security->isGranted('MOO_ER')
            || (
                $security->isGranted('FEATURE_PRE_DELIVERY_INSPECTION_WRITE')
                && (
                    $user->getBusinessUnit()->getLocation() === $subject->getManufacturerLocation()
                    || PeopleManager::isAsmOfCustomerOrParent($user, $subject->getBuyer())
                )
            )
        ;
    }
}
