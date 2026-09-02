<?php

declare(strict_types=1);

namespace App\EventListener\MinutesOfMeeting;

use App\Entity\MinutesOfMeeting\Meeting;
use App\Event\Activity\SubscriptionCreatedEvent;
use App\Notifier\MinutesOfMeeting\MinutesOfMeetingNotifier;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class MeetingSubscriptionListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SubscriptionCreatedEvent::class => ['onSubscription'],
        ];
    }

    public function onSubscription(SubscriptionCreatedEvent $event)
    {
        $meeting = $event->getItem();

        if (!$meeting instanceof Meeting || Meeting::RELEASED !== $meeting->getStatus()) {
            return;
        }

        $this->serviceLocator->get(MinutesOfMeetingNotifier::class)->sendReleasedSubscriptionEmail($meeting, $event->getSubscription()->getUser());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            MinutesOfMeetingNotifier::class,
        ];
    }
}
