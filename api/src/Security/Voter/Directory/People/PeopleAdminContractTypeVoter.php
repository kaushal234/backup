<?php

declare(strict_types=1);

namespace App\Security\Voter\Directory\People;

use App\Entity\Directory\People;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class PeopleAdminContractTypeVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'PEOPLE_CONTRACT_TYPE_ADMIN_VOTER' === $attribute && $subject instanceof People;
    }

    /**
     * @param People $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof People) {
            return false;
        }

        if (null === $businessUnit = $subject->getBusinessUnit()) {
            return false;
        }

        $security = $this->getSecurity();

        return $security->isGranted('CONTRACT_TYPE_ADMIN_VOTER') || $security->isGranted(\sprintf('FEATURE_PEOPLE_UPDATE_%s', $businessUnit->getLocation()->getId()));
    }
}
