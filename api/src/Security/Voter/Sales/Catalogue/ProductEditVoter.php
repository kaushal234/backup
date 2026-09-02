<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\Catalogue;

use App\Entity\Directory\People;
use App\Entity\Sales\Product;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ProductEditVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'CATALOG_EDIT_VOTER' === $attribute && $subject instanceof Product;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        foreach (['ROLE_PSM', 'ROLE_PSE', 'ROLE_PSA'] as $role) {
            if ($this->getSecurity()->isGranted($role, $subject->getErpLocation())) {
                return true;
            }
        }

        return false;
    }
}
