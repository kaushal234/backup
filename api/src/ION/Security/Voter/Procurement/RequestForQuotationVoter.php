<?php

declare(strict_types=1);

namespace App\ION\Security\Voter\Procurement;

use App\Entity\Purchasing\VendorUser;
use App\ION\Resources\Procurement\RequestForQuotation;
use App\Security\Voter\AbstractVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class RequestForQuotationVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'REQUEST_FOR_QUOTATION_VOTER' === $attribute && $subject instanceof RequestForQuotation;
    }

    /**
     * @param RequestForQuotation $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $this->serviceLocator->get(Security::class)->getUser();
        if (!$user instanceof VendorUser) {
            return false;
        }

        foreach ($user->contact->getBusinessPartners() as $businessPartner) {
            foreach ($subject->getBidders() as $bidder) {
                if ($businessPartner->code === $bidder->bidderCode) {
                    return true;
                }
            }
        }

        return false;
    }
}
