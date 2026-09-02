<?php

declare(strict_types=1);

namespace App\Security\Voter\Survey;

use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Survey\PublishedSurvey;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class SurveyViewVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'SURVEY_VIEW_VOTER' === $attribute && $subject instanceof PublishedSurvey;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        $security = $this->getSecurity();
        if ($security->isGranted('FEATURE_SURVEY_VIEW', $subject)) {
            return true;
        }

        if (!($target = $subject->getTarget()) instanceof ExtranetUser) {
            return false;
        }

        foreach ($target->getExtranetUserAcls() as $acl) {
            $crt = $acl->getCrt();
            if ($user === $crt->getPartsRepresentative()) {
                return true;
            }
            if ($user === $crt->getServiceRepresentative()) {
                return true;
            }
        }

        return false;
    }
}
