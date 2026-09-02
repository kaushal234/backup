<?php

declare(strict_types=1);

namespace App\Security\Voter\MinutesOfMeeting;

use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Meeting;
use App\Repository\Common\SubscriptionRepository;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class MeetingAccessVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[SubscriptionRepository::class]];
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'MEETING_READ_VOTER' === $attribute && $subject instanceof Meeting;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (!($user = $token->getUser()) instanceof People) {
            return false;
        }

        if (false === $subject->isConfidential()) {
            return true;
        }

        $security = $this->getSecurity();
        if ($security->isGranted('FEATURE_MEETING_READ', $subject)) {
            return true;
        }

        if ($security->isGranted('MOO_MOM')) {
            return true;
        }

        if (
            $user === ($poster = $subject->getCreatedBy())
            || $user === ($supervisor = $poster->getSupervisor())
            || (null !== $supervisor && $user === $supervisor->getSupervisor())
        ) {
            return true;
        }

        if (!$subject->getAttendees()->filter(static fn (People $people) => $people === $user)->isEmpty()) {
            return true;
        }

        return (bool) $this->serviceLocator->get(SubscriptionRepository::class)->isFollowingResource($user, $subject);
    }
}
