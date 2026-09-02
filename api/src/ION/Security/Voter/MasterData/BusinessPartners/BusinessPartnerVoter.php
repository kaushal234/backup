<?php

declare(strict_types=1);

namespace App\ION\Security\Voter\MasterData\BusinessPartners;

use App\Entity\Purchasing\VendorUser;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerGetterInterface;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class BusinessPartnerVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'BUSINESS_PARTNER_VOTER' === $attribute && $subject instanceof BusinessPartnerGetterInterface;
    }

    /**
     * @param BusinessPartnerGetterInterface $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $this->getSecurity()->getUser();
        if (!$user instanceof VendorUser) {
            return false;
        }

        if (null === $subject->getBusinessPartnerCode()) {
            return false;
        }

        return !$user->contact->getBusinessPartners()
            ->filter(static fn (BusinessPartner $businessPartner) => $businessPartner->code === $subject->getBusinessPartnerCode())
            ->isEmpty()
        ;
    }
}
