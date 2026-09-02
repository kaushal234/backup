<?php

declare(strict_types=1);

namespace App\EventListener\MinutesOfMeeting;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Common\Subscription;
use App\Entity\MinutesOfMeeting\Action;
use App\Entity\MinutesOfMeeting\Meeting;
use App\Event\Activity\CommentCreatedEvent;
use App\Notifier\MinutesOfMeeting\MinutesOfMeetingNotifier;
use App\Request\Activity\CommentRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class MeetingWriteListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedServices(): array
    {
        return [
            MinutesOfMeetingNotifier::class,
            IriConverterInterface::class,
            EntityManagerInterface::class,
            CommentRequestManager::class,
            TaskManager::class,
            Security::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => ['notifyMeetingFollowers'],
            KernelEvents::VIEW => [
                ['onMeetingPreWrite', EventPriorities::PRE_WRITE],
                ['onMeetingPostWrite', EventPriorities::POST_WRITE],
                ['onMeetingDuplicateWrite', EventPriorities::POST_WRITE],
                ['onMeetingDelete', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public function onMeetingPreWrite(ViewEvent $event)
    {
        $meeting = $event->getControllerResult();

        if (!$meeting instanceof Meeting || !$event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        if (null === $creator = $this->serviceLocator->get(Security::class)->getUser()) {
            return;
        }

        if (!$meeting->getAttendees()->contains($creator)) {
            $meeting->addAttendee($creator);
        }

        if ($meeting->isQuick()) {
            $meeting->setStatus(Meeting::CLOSED);
        }
    }

    public function onMeetingPostWrite(ViewEvent $event)
    {
        $meeting = $event->getControllerResult();
        $request = $event->getRequest();
        if (!$meeting instanceof Meeting) {
            return;
        }

        $notifier = $this->serviceLocator->get(MinutesOfMeetingNotifier::class);
        switch ($event->getRequest()->getMethod()) {
            case Request::METHOD_POST:
                $notifier->sendCreationEmail($meeting);
                break;
            case Request::METHOD_PUT:
                if ('update_meeting_status' !== $request->attributes->get('_route')) {
                    return;
                }
                $notifier->sendStatusEmail($meeting);
                break;
            default:
                return;
        }
    }

    public function notifyMeetingFollowers(CommentCreatedEvent $event)
    {
        $item = $event->getItem();
        if (!$item instanceof Meeting || !$event->isMainRequest()) {
            return;
        }

        $notifier = $this->serviceLocator->get(MinutesOfMeetingNotifier::class);
        $notifier->sendCommentEmail($item, $event->getComment());
    }

    public function onMeetingDuplicateWrite(ViewEvent $event)
    {
        $meeting = $event->getControllerResult();

        if (!$meeting instanceof Meeting) {
            return;
        }

        $request = $event->getRequest();
        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        if ('duplicate_meeting' !== $operation->getName()) {
            return;
        }

        /** @var Meeting $previousMeeting */
        $previousMeeting = $request->attributes->get('previous_data');

        $commentManager = $this->serviceLocator->get(CommentRequestManager::class);

        $commentManager->insertComment($meeting, \sprintf('This meeting has been created by duplicating the meeting #%s', $previousMeeting->getId()));

        $iriConverter = $this->serviceLocator->get(IriConverterInterface::class);
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $subscriptionRepository = $entityManager->getRepository(Subscription::class);

        $subscriptions = $subscriptionRepository->findByResource($previousMeeting);
        $iri = $iriConverter->getIriFromResource($meeting);

        foreach ($subscriptions as $previousSubscription) {
            $subscription = new Subscription();
            $subscription
                ->setUser($previousSubscription->getUser())
                ->setResource($iri)
            ;
            $entityManager->persist($subscription);
        }

        $entityManager->flush();
    }

    public function onMeetingDelete(ViewEvent $event)
    {
        $meeting = $event->getControllerResult();

        if (!$meeting instanceof Meeting) {
            return;
        }

        if (Request::METHOD_DELETE !== $event->getRequest()->getMethod()) {
            return;
        }

        $taskManager = $this->serviceLocator->get(TaskManager::class);
        $user = $this->serviceLocator->get(Security::class)->getUser();
        if (null === $user) {
            return;
        }

        $actionsRepository = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(Action::class);
        $actions = $meeting->getActions();
        foreach ($actions as $action) {
            if (1 === $actionsRepository->count(['task' => $action->getTask()])) {
                $taskManager->close((new Task())->setId($action->getTask()), 'This MOM has been deleted', $user);
            }
        }
    }
}
