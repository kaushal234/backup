<?php

declare(strict_types=1);

namespace App\Security\Voter\PowerBI;

use App\Entity\Directory\People;
use App\Entity\PowerBI\Report;
use App\Manager\Directory\PeopleManager;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ReportReadVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'FEATURE_POWER_BI_REPORT_READ_VOTER' === $attribute && $subject instanceof Report;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        if (PeopleManager::hasGroup($user, 'SUPERUSER')) {
            return true;
        }

        $security = $this->getSecurity();
        $reportGroups = $subject->getGroups();

        if (0 === $reportGroups->count()) {
            return $security->isGranted('FEATURE_POWER_BI_REPORT_READ');
        }

        $isAuthorized = false;
        foreach ($user->getValidGroups() as $userGroup) {
            if ($reportGroups->contains($userGroup)) {
                $isAuthorized = true;
            }
        }

        return $isAuthorized && $security->isGranted('FEATURE_POWER_BI_REPORT_READ');
    }
}
