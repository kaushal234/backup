<?php

declare(strict_types=1);

namespace App\Security\Voter\Purchasing\VendorWarrantyClaim;

use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorWarrantyClaimThreshold;
use App\Manager\Directory\PeopleManager;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class VendorWarrantyClaimThresholdWriteVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'VENDOR_WARRANTY_CLAIM_THRESHOLD_WRITE_VOTER' === $attribute && $subject instanceof VendorWarrantyClaimThreshold;
    }

    /**
     * {@inheritdoc}
     *
     * @param VendorWarrantyClaimThreshold $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof People) {
            return false;
        }

        $security = $this->getSecurity();

        return PeopleManager::hasGroup($user, 'SUPERUSER')
            || $security->isGranted('MOO_VWC')
            || $security->isGranted('ROLE_QAM', $subject->location);
    }
}
