<?php

declare(strict_types=1);

namespace App\Security\Voter\Common;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Meeting;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class MeetingSubscriptionVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[IriConverterInterface::class]];
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, ['SUBSCRIPTION_DELETE_VOTER', 'SUBSCRIPTION_CREATE_VOTER'], true)
            && $subject instanceof Subscription
            && 0 === mb_strpos($subject->getResource(), '/minutes_of_meeting/meetings')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $subscriber = $subject->getUser();

        if (!$subscriber instanceof People) {
            return false;
        }

        try {
            $item = $this->serviceLocator->get(IriConverterInterface::class)->getResourceFromIri($subject->getResource());
        } catch (\Exception $exception) {
            return false;
        }

        if (!$item instanceof Meeting) {
            return false;
        }

        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        $isGrantedMeetingWrite = $this->getSecurity()->isGranted('MEETING_WRITE_VOTER', $item);

        if ('SUBSCRIPTION_DELETE_VOTER' === $attribute) {
            return $isGrantedMeetingWrite || $user === $subscriber;
        }

        return $isGrantedMeetingWrite;
    }
}
