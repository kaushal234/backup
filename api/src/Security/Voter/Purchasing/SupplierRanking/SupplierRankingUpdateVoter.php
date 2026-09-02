<?php

declare(strict_types=1);

namespace App\Security\Voter\Purchasing\SupplierRanking;

use App\Entity\Purchasing\Supplier\Supplier;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

/**
 * This voter allow user with FEATURE_SUPPLIER_RANKING_UPDATE to update their supplier ranking associated to his location.
 */
class SupplierRankingUpdateVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'SUPPLIER_RANKING_UPDATE_VOTER' === $attribute && $subject instanceof SupplierRanking;
    }

    /**
     * @param SupplierRanking $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (!$subject->supplier instanceof Supplier) {
            return false;
        }

        if (null === $subject->supplier->location) {
            return false;
        }

        return $this->getSecurity()->isGranted('FEATURE_SUPPLIER_RANKING_UPDATE_'.$subject->supplier->location->getId());
    }
}
