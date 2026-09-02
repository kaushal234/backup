<?php

declare(strict_types=1);

namespace App\Security\Voter\Finance;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Finance\ManufacturingMargin;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ManufacturingMarginViewVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'MANUFACTURING_MARGIN_VIEW_VOTER' === $attribute && $subject instanceof ManufacturingMargin;
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

        $security = $this->getSecurity();
        if ($security->isGranted('FEATURE_MANUFACTURING_MARGIN_VIEW_FULL') || $security->isGranted('MOO_RRR')) {
            return true;
        }

        /** @var Location $location */
        $location = $subject->getEquipmentRecord()->getManufacturerLocation();
        $security = $this->getSecurity();

        return null !== $location && $security->isGranted(\sprintf('FEATURE_MANUFACTURING_MARGIN_VIEW_LOCATION_%s', $location->getId()));
    }
}
