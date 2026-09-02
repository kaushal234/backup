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

class VendorWarrantyClaimStatusVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'VENDOR_WARRANTY_CLAIM_STATUS_VOTER' === $attribute && $subject instanceof VendorWarrantyClaim;
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

        if ($user instanceof VendorUser
            && VendorWarrantyClaimStatus::VENDOR_TO_RESPOND !== $subject->status->name
            && $this->getSecurity()->isGranted('BUSINESS_PARTNER_VOTER', $subject)
        ) {
            return false;
        }

        if (VendorWarrantyClaimStatus::QA_ANALYSIS === $subject->status->name && !$this->getSecurity()->isGranted('FEATURE_VENDOR_WARRANTY_CLAIM_QUALITY')) {
            return false;
        }

        return !(\in_array($subject->status->name, VendorWarrantyClaimStatus::CLOSED_STATUSES, true) && !$this->getSecurity()->isGranted('FEATURE_VENDOR_WARRANTY_CLAIM_REOPEN'));
    }
}
