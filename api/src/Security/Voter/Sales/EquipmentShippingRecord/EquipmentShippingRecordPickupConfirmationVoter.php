<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\EquipmentShippingRecord;

use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class EquipmentShippingRecordPickupConfirmationVoter extends AbstractVoter
{
    public const EDIT_PICKUP_CONFIRMATION = 'EDIT_PICKUP_CONFIRMATION';

    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[EntityManagerInterface::class]];
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::EDIT_PICKUP_CONFIRMATION === $attribute
            && ($subject instanceof EquipmentShippingRecord || $subject instanceof EquipmentShippingRecordLine);
    }

    /**
     * @param EquipmentShippingRecord|EquipmentShippingRecordLine $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $security = $this->getSecurity();

        if ($security->isGranted('MOO_ESR')) {
            return true;
        }

        /** @var EntityManagerInterface $em */
        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $uow = $em->getUnitOfWork();

        $lines = $subject instanceof EquipmentShippingRecord
            ? $subject->getEquipmentShippingRecordLines()
            : [$subject];

        foreach ($lines as $line) {
            // As soon as one line has a pickup confirmation change,
            // we require the dedicated feature.
            if ($this->hasPickupChangeOnLine($line, $uow)) {
                return $security->isGranted('FEATURE_EQUIPMENT_SHIPPING_RECORD_LINE_PICK_UP_CONFIRMATION');
            }
        }

        return true;
    }

    /**
     * Checks if estimatedPickUpDateConfirmation has changed for a single line.
     */
    private function hasPickupChangeOnLine(EquipmentShippingRecordLine $line, UnitOfWork $uow): bool
    {
        if (UnitOfWork::STATE_NEW === $uow->getEntityState($line)) {
            return true === $line->estimatedPickUpDateConfirmation;
        }

        $originalData = $uow->getOriginalEntityData($line);

        $oldValue = (bool) ($originalData['estimatedPickUpDateConfirmation'] ?? false);
        $newValue = $line->estimatedPickUpDateConfirmation;

        return $oldValue !== $newValue;
    }
}
