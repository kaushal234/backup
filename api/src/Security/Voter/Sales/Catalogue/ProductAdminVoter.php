<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\Catalogue;

use App\Entity\Directory\People;
use App\Entity\Sales\Product;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ProductAdminVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'CATALOG_ADMIN_VOTER' === $attribute && $subject instanceof Product;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        $security = $this->getSecurity();

        return $security->isGranted('FEATURE_CATALOG_EDIT')
            || $security->isGranted('FEATURE_CATALOG_FINANCE_ADMIN')
            || $security->isGranted('FEATURE_PRODUCT_MANUFACTURING_WRITE')
            || $security->isGranted('FEATURE_PRODUCT_STANDARD_ITEM_WRITE')
            || $security->isGranted('MOO_CAT')
            || $security->isGranted('CATALOG_EDIT_VOTER', $subject)
        ;
    }
}
