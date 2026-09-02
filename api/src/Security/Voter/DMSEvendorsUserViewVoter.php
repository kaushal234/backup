<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DMS;
use App\Entity\Purchasing\VendorUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class DMSEvendorsUserViewVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof DMS && 'EVENDORS_DMS_VIEW_VOTER' === $attribute;
    }

    /**
     * {@inheritdoc}
     *
     * @param DMS $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof VendorUser) {
            return false;
        }

        return $this->getSecurity()->isGranted('ACCESS_VENDOR_USER')
            && !$subject->isConfidential()
            && 'EVENDOR' === $subject->getPortal()
        ;
    }
}
