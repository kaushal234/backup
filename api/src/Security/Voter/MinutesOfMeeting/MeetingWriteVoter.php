<?php

declare(strict_types=1);

namespace App\Security\Voter\MinutesOfMeeting;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Dto\TaskInput;
use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Meeting;
use App\Security\Voter\AbstractVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class MeetingWriteVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), IriConverterInterface::class];
    }

    protected function supports(string $attribute, $subject): bool
    {
        return 'MEETING_WRITE_VOTER' === $attribute && ($subject instanceof Meeting || $subject instanceof TaskInput);
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
        if ($security->isGranted('FEATURE_MEETING_WRITE')) {
            return true;
        }

        if ($security->isGranted('MOO_MOM')) {
            return true;
        }

        if ($subject instanceof TaskInput && null === ($subject->getResource() ?? null)) {
            return false;
        }

        /** @var Meeting $meeting */
        $meeting = $subject instanceof TaskInput ? $this->serviceLocator->get(IriConverterInterface::class)->getResourceFromIri($subject->getResource()) : $subject;

        /** @var ArrayCollection */
        $attendees = $meeting->getAttendees();

        if ($attendees->contains($user)) {
            return true;
        }

        return
            $user === ($poster = $meeting->getCreatedBy())
            || $user === ($supervisor = $poster->getSupervisor())
            || (null !== $supervisor && $user === $supervisor->getSupervisor())
        ;
    }
}
