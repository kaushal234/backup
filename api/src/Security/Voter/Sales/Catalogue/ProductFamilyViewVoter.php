<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\Catalogue;

use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ProductFamily;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ProductFamilyViewVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'PRODUCT_FAMILY_VIEW_VOTER' === $attribute && $subject instanceof ProductFamily;
    }

    /**
     * @param ProductFamily $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People && !$user instanceof ExtranetUser) {
            return false;
        }

        $security = $this->getSecurity();
        if ($security->isGranted('ACCESS_PEOPLE')) {
            return true;
        }

        return $security->isGranted('ACCESS_EXTRANET_USER')
            && !$subject->isHidden()
            && ($subject->isPublicForSAS() || $subject->isPublicForTLD() || $subject->isPublicForAerospecialties())
        ;
    }
}
