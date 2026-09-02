<?php

declare(strict_types=1);

namespace App\Security\Voter\Directory\ContractType;

use App\Entity\Directory\People;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ContractTypeVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'CONTRACT_TYPE_ADMIN_VOTER' === $attribute;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof People) {
            return false;
        }

        return $this->getSecurity()->isGranted('FEATURE_CONTRACT_TYPE_ADMIN')
            || $this->getSecurity()->isGranted('MOO_USER')
            || $this->getSecurity()->isGranted('MOO_ESM')
        ;
    }
}
