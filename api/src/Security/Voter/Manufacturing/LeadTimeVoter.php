<?php

declare(strict_types=1);

namespace App\Security\Voter\Manufacturing;

use App\Entity\Directory\People;
use App\Entity\Manufacturing\LeadTime;
use App\Manager\Directory\PeopleManager;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class LeadTimeVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'LEAD_TIME_WRITE_VOTER' === $attribute && $subject instanceof LeadTime;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        $security = $this->getSecurity();
        if (PeopleManager::hasOneOfGroups(
            $user,
            ['ROLE_PSM', 'ROLE_PSA', 'ROLE_PSE', 'ROLE_COO'],
            $subject->factory)) {
            return true;
        }

        return $security->isGranted('MOO_SLT') || $security->isGranted('FEATURE_LEAD_TIME_WRITE_ADMIN');
    }
}
