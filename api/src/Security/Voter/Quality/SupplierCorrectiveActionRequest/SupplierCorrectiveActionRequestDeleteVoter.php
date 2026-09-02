<?php

declare(strict_types=1);

namespace App\Security\Voter\Quality\SupplierCorrectiveActionRequest;

use App\Entity\Directory\People;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class SupplierCorrectiveActionRequestDeleteVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'SUPPLIER_CORRECTIVE_ACTION_REQUEST_DELETE_VOTER' === $attribute && $subject instanceof SupplierCorrectiveActionRequest;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        $security = $this->getSecurity();

        return $security->isGranted('FEATURE_SCAR_DELETE') || $security->isGranted(\sprintf('FEATURE_SCAR_DELETE_FACTORY_%d', $subject->factory->getId()));
    }
}
