<?php

declare(strict_types=1);

namespace App\EventListener\Task;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Task\PartNumberTask;
use App\Entity\Task\Task;
use App\Factory\Common\Notification\AbstractNotificationFactory;
use App\Factory\Common\Notification\Task\TaskAssignedNotificationFactory;
use App\Factory\Common\Notification\Task\TaskClosedNotificationFactory;
use App\Factory\Common\Notification\Task\TaskPauseNotificationFactory;
use App\Factory\Common\Notification\Task\TaskRescheduledNotificationFactory;
use App\Notifier\Tasks\TaskNotifier;
use App\Request\Activity\CommentRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

readonly class TaskListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private const array MODULES_USING_DEDICATED_LOCATION = [
        Task::WHT,
        PartNumberTask::PNT,
    ];

    public function __construct(
        private ContainerInterface $serviceLocator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onPostWrite', EventPriorities::POST_WRITE],
                ['setEscalationDate', EventPriorities::POST_DESERIALIZE],
            ],
        ];
    }

    public function onPostWrite(ViewEvent $event): void
    {
        $task = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$task instanceof Task
            || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            return;
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        $usesDedicatedLocation = \in_array(
            $task->module?->getName(),
            self::MODULES_USING_DEDICATED_LOCATION,
            true
        );

        if (!$usesDedicatedLocation) {
            $location = $task->createdBy?->getBusinessUnit()?->getLocation();

            if (null === $location) {
                return;
            }

            $task->location = $location;
        }

        $user = $this->serviceLocator->get(Security::class)->getUser();
        $route = $request->attributes->get('_route');

        if (null !== $task->comment) {
            $metadata = [];
            foreach ($task->getRecipients() as $cc) {
                $metadata['recipients'][] = $cc->getEmail();
            }

            $file = $request->files->get('file');
            $this->serviceLocator->get(CommentRequestManager::class)->insertComment($task, $task->comment, $file, $metadata);
        }

        if (\in_array($route, ['task_close_comment', 'task_transfer', 'task_reschedule', 'task_pause_comment'], true)) {
            switch ($route) {
                case 'task_close_comment':
                    $notificationTemplateFactory = TaskClosedNotificationFactory::class;
                    $recipients = [$task->assignee, ...$task->getRecipients()];
                    break;
                case 'task_reschedule':
                    $notificationTemplateFactory = TaskRescheduledNotificationFactory::class;
                    $recipients = [$task->assignee, ...$task->getRecipients()];
                    break;
                case 'task_pause_comment':
                    $notificationTemplateFactory = TaskPauseNotificationFactory::class;
                    $recipients = [$task->assignee, ...$task->getRecipients()];
                    break;
                default:
                    $notificationTemplateFactory = TaskAssignedNotificationFactory::class;
                    $recipients = [$task->assignee];
            }

            foreach (array_unique($recipients) as $recipient) {
                if ($recipient === $user) {
                    continue;
                }
                /** @var AbstractNotificationFactory $factory */
                $factory = $this->serviceLocator->get($notificationTemplateFactory);
                $notification = $factory->createNotification($task, $recipient);

                $entityManager->persist($notification);
            }

            $entityManager->flush();

            return;
        }

        $entityManager->flush();
        $condition = match ($route) {
            '_api_/tasks/{id}{._format}_put' => 'update',
            'task_comment' => 'comment',
            'task_reopen' => 'reopen',
            default => 'creation',
        };

        $this->serviceLocator->get(TaskNotifier::class)->sendEmail($task, $condition, ['user' => $user->getDisplayName()]);
    }

    public function setEscalationDate(ViewEvent $event): void
    {
        $task = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$task instanceof Task
            || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            return;
        }

        /*
         * Set the escalationDate by adding the escalationTrigger and escalationTriggerUnit to the dueDate.
         */
        if (null === $task->escalationDate) {
            $task->escalationDate = (new \DateTime($task->dueDate->format('Y-m-d H:i:s')))->modify(\sprintf('+ %d %s', $task->escalationTrigger, $task->escalationTriggerUnit));
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            TaskNotifier::class,
            TaskClosedNotificationFactory::class,
            TaskRescheduledNotificationFactory::class,
            TaskAssignedNotificationFactory::class,
            TaskPauseNotificationFactory::class,
            CommentRequestManager::class,
            Security::class,
            EntityManagerInterface::class,
        ];
    }
}
