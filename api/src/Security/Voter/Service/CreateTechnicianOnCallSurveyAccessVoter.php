<?php

declare(strict_types=1);

namespace App\Security\Voter\Service;

use App\Entity\Service\TechnicianOnCall\TechnicianOnCallSurvey;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class CreateTechnicianOnCallSurveyAccessVoter extends AbstractVoter
{
    public function supports(string $attribute, $subject): bool
    {
        return 'CREATE_TECHNICIAN_ON_CALL_SURVEY' === $attribute && $subject instanceof TechnicianOnCallSurvey;
    }

    /**
     * @param TechnicianOnCallSurvey $subject
     */
    public function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if ($this->getSecurity()->isGranted('FEATURE_TECHNICIAN_ON_CALL_SURVEY')) {
            return true;
        }

        if (null === $subject->technicianOnCall->token || null === $subject->token) {
            return false;
        }

        return $subject->token === $subject->technicianOnCall->token;
    }

    public static function getSubscribedServices(): array
    {
        return [
            ...parent::getSubscribedServices(),
            EntityManagerInterface::class,
        ];
    }
}
