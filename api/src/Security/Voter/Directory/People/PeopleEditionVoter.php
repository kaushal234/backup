<?php

declare(strict_types=1);

namespace App\Security\Voter\Directory\People;

use App\Entity\Directory\People;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class PeopleEditionVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof People && 'FEATURE_PEOPLE_UPDATE_VOTER' === $attribute;
    }

    /**
     * {@inheritdoc}
     *
     * @param People $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var People $user */
        $user = $token->getUser();

        if (!$user instanceof People) {
            return false;
        }

        $security = $this->getSecurity();

        return $user->getUserIdentifier() === $subject->getUserIdentifier()
            || (null !== $subject->getSupervisor() && $user->getUserIdentifier() === $subject->getSupervisor()->getUserIdentifier())
            || ($subject->getBusinessUnit() === $user->getBusinessUnit() && $security->isGranted('ROLE_INTRANET_ADMIN', $user->getBusinessUnit()->getLocation()))
            || $security->isGranted('PEOPLE_PARTIAL_UPDATE_VOTER', $subject)
            || $security->isGranted('CONTRACT_TYPE_ADMIN_VOTER')
            || $security->isGranted('FEATURE_PEOPLE_UPDATE')
        ;
    }
}
