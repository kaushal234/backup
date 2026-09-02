<?php

declare(strict_types=1);

namespace App\Security\Voter\Purchasing\VendorWarrantyClaim;

use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class VendorWarrantyClaimVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'VENDOR_WARRANTY_CLAIM_VOTER' === $attribute && $subject instanceof VendorWarrantyClaim;
    }

    /**
     * {@inheritdoc}
     *
     * @param VendorWarrantyClaim $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People && !$user instanceof VendorUser) {
            return false;
        }

        $security = $this->getSecurity();

        return ($security->isGranted('FEATURE_VENDOR_WARRANTY_CLAIM_EDIT_SHIPPING')
            || $security->isGranted('FEATURE_VWC_EDIT_FINANCE')
            || $security->isGranted('FEATURE_VENDOR_WARRANTY_CLAIM_EDIT')
            || ($security->isGranted('BUSINESS_PARTNER_VOTER', $subject) && VendorWarrantyClaimStatus::VENDOR_TO_RESPOND === $subject->status->name))
            && !\in_array($subject->status->name, VendorWarrantyClaimStatus::CLOSED_STATUSES, true)
        ;
    }
}
