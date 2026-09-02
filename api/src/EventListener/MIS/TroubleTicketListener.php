<?php

declare(strict_types=1);

namespace App\EventListener\MIS;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Activity\Comment;
use App\Entity\BaseTask;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\MIS\TroubleTicket\Type;
use App\Factory\Common\Notification\MIS\TroubleTicketAssignedNotificationFactory;
use App\Factory\Common\Notification\MIS\TroubleTicketClosedNotificationFactory;
use App\Factory\EmailChangeSetFactory;
use App\Jira\DataProvider\JiraItemDataProvider;
use App\Jira\Resources\TroubleTicketIssue;
use App\Manager\MIS\TroubleTicket\TroubleTicketManager;
use App\Notifier\MIS\TroubleTicket\RecipientsFinder;
use App\Notifier\MIS\TroubleTicket\TroubleTicketNotifier;
use App\Request\Activity\CommentRequestManager;
use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class TroubleTicketListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator,
        private array $changeSet = [],
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onPostRead', EventPriorities::POST_READ],
            ],
            KernelEvents::VIEW => [
                ['onPreUpdate', EventPriorities::PRE_WRITE],
                ['onPostUpdate', EventPriorities::POST_WRITE],
                ['onPreCreate', EventPriorities::PRE_WRITE],
                ['onPostCreate', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onPostRead(RequestEvent $event)
    {
        $troubleTicket = $event->getRequest()->attributes->get('data');
        $request = $event->getRequest();
        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        if (!$troubleTicket instanceof TroubleTicket || !$operation instanceof Get) {
            return;
        }

        if (null === $troubleTicket->jiraIssueNumber) {
            return;
        }

        /** @var ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory */
        $resourceMetadataFactory = $this->serviceLocator->get(ResourceMetadataCollectionFactoryInterface::class);
        $metadata = $resourceMetadataFactory->create(TroubleTicketIssue::class);

        $troubleTicket->issue = $this->serviceLocator->get(JiraItemDataProvider::class)->provide($metadata->getOperation(), ['id' => $troubleTicket->jiraIssueNumber]);
    }

    public function onPostUpdate(ViewEvent $event): void
    {
        $troubleTicket = $event->getControllerResult();
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        if (!$troubleTicket instanceof TroubleTicket
            || Request::METHOD_GET === $request->getMethod()
            || Request::METHOD_PUT !== $request->getMethod()
            && (Request::METHOD_POST === $request->getMethod() && !\in_array($route, ['comment_trouble_ticket', 'transfer_trouble_ticket', 'reopen_trouble_ticket'], true))
        ) {
            return;
        }

        if (null !== $troubleTicket->comment) {
            $file = $request->files->get('file');

            $metadata = [];
            foreach ($troubleTicket->getCcs() as $cc) {
                $metadata['recipients'][] = $cc->getEmail();
            }

            $this->serviceLocator->get(CommentRequestManager::class)->insertComment($troubleTicket, $troubleTicket->comment, $file, $metadata);
            $troubleTicket->lastCommentedAt = new \DateTime();
            $entityManager->persist($troubleTicket);
            $entityManager->flush();
        }

        switch (true) {
            case 'transfer_trouble_ticket' === $route:
                $subject = 'transfer';
                break;
            case 'comment_trouble_ticket' === $route && !\in_array($troubleTicket->getStatus(), TroubleTicket::CLOSED_STATUSES, true):
                $subject = 'comment';
                break;
            case \in_array($troubleTicket->getStatus(), TroubleTicket::CLOSED_STATUSES, true):
                $troubleTicket->closedAt = new \DateTime();

                $entityManager->persist($troubleTicket);
                $this->createNotification($troubleTicket, TroubleTicketClosedNotificationFactory::class, true);

                return;
            case $request->isMethod(Request::METHOD_PUT):
                $subject = 'update';
                break;
            default:
                return;
        }

        $workflowStatusUpdater = $this->serviceLocator->get(WorkflowStatusUpdater::class);
        /** @var TroubleTicket $previousData */
        $previousData = $request->attributes->get('previous_data');
        if ($previousData->type !== $troubleTicket->type || $previousData->module !== $troubleTicket->module) {
            // A TTS whose creator is the MOO or GKU of the (new) module needs no MOO/GKU validation: it goes straight to MIS.
            $creatorIsMooOrGku = null !== $troubleTicket->createdBy
                && ($troubleTicket->createdBy === $troubleTicket->module->getOperationalOwner() || $troubleTicket->createdBy === $troubleTicket->module->getKeyUser());

            switch (true) {
                case $creatorIsMooOrGku:
                    $status = TroubleTicket::PENDING;
                    break;
                case Type::REQUEST === $troubleTicket->type->type:
                case Type::INCIDENT === $troubleTicket->type->type && !$troubleTicket->module->isMisRelative():
                    $status = TroubleTicket::PENDING_MOO;
                    break;
                default:
                    $status = TroubleTicket::PENDING;
            }

            $troubleTicket->setStatus($status);
            $troubleTicket->assignee = $creatorIsMooOrGku ? null : $this->serviceLocator->get(TroubleTicketManager::class)->getDefaultAssignee($troubleTicket);
            if (null !== $troubleTicket->misAssignee && Type::REQUEST === $troubleTicket->type->type) {
                $troubleTicket->misAssignee = null;
            }

            if (null !== $troubleTicket->assignee && $troubleTicket->assignee !== $previousData->assignee) {
                $this->createNotification($troubleTicket, TroubleTicketAssignedNotificationFactory::class);
            }
        }

        if (null === $previousData->misAssignee && null !== $troubleTicket->misAssignee && TroubleTicket::PENDING === $troubleTicket->getStatus()) {
            try {
                $workflowStatusUpdater->applyStatus($troubleTicket, TroubleTicket::IN_PROGRESS);
            } catch (LogicException $logicException) {
                throw new BadRequestHttpException($logicException->getMessage(), $logicException);
            }
        }

        $entityManager->persist($troubleTicket);
        $entityManager->flush();

        /** @var People|null $user */
        $user = $this->serviceLocator->get(Security::class)->getUser();
        if (null === $user) {
            return;
        }

        // Nothing left to notify about once supportLevel is excluded (e.g. only the support level was changed)
        if ('update' === $subject && empty($this->changeSet['changeSet'] ?? [])) {
            return;
        }

        $this->serviceLocator->get(TroubleTicketNotifier::class)->sendNotification($troubleTicket, $subject, $user, $this->changeSet);
    }

    public function onPreCreate(ViewEvent $event): void
    {
        $troubleTicket = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$troubleTicket instanceof TroubleTicket || !$request->isMethod(Request::METHOD_POST) || \in_array($request->attributes->get('_route'), ['transfer_trouble_ticket', 'comment_trouble_ticket'], true)) {
            return;
        }

        if (null === $troubleTicket->createdBy) {
            /** @var People $user */
            $user = $this->serviceLocator->get(Security::class)->getUser();
            $troubleTicket->createdBy = $user;
        }

        // A TTS created by the module's own MOO or GKU needs no MOO/GKU validation: it goes straight to MIS.
        $creatorIsMooOrGku = null !== $troubleTicket->createdBy
            && ($troubleTicket->createdBy === $troubleTicket->module->getOperationalOwner() || $troubleTicket->createdBy === $troubleTicket->module->getKeyUser());

        if (!$creatorIsMooOrGku && (Type::REQUEST === $troubleTicket->type->type || (Type::INCIDENT === $troubleTicket->type->type && !$troubleTicket->module->isMisRelative()))) {
            $troubleTicket->setStatus(TroubleTicket::PENDING_MOO);
        }

        $troubleTicket->assignee = $creatorIsMooOrGku ? null : $this->serviceLocator->get(TroubleTicketManager::class)->getDefaultAssignee($troubleTicket);

        if (null === $troubleTicket->assignee) {
            $troubleTicket->setStatus(BaseTask::PENDING);
        }
        $troubleTicket->dueDate = (new \DateTime())->modify('+ 30 days');
    }

    public function onPreUpdate(ViewEvent $event): void
    {
        $troubleTicket = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$troubleTicket instanceof TroubleTicket || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        // supportLevel changes must not trigger notifications to TTS recipients
        $this->changeSet = ['changeSet' => $this->serviceLocator->get(EmailChangeSetFactory::class)->createChangeSetForEmail($troubleTicket, ['supportLevel'], 'Y-m-d')];
    }

    public function onPostCreate(ViewEvent $event): void
    {
        $troubleTicket = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$troubleTicket instanceof TroubleTicket || !$request->isMethod(Request::METHOD_POST) || \in_array($request->attributes->get('_route'), ['transfer_trouble_ticket', 'comment_trouble_ticket'], true)) {
            return;
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        if (Type::SECURITY_HIGH_ATTENTION === $troubleTicket->type->description) {
            $comment = (new Comment())
                ->setMessage(Type::SECURITY_INCIDENT_TEMPLATE)
                ->setResource($this->serviceLocator->get(IriConverterInterface::class)->getIriFromResource($troubleTicket))
                ->setUser(null)
            ;
            $entityManager->persist($comment);
            $entityManager->flush();
        }

        /** @var People|null $user */
        $user = $this->serviceLocator->get(Security::class)->getUser();
        if (null === $user) {
            return;
        }
        $this->serviceLocator->get(TroubleTicketNotifier::class)->sendNotification($troubleTicket, 'creation', $user);

        if (null !== $troubleTicket->assignee) {
            $notification = $this->serviceLocator->get(TroubleTicketAssignedNotificationFactory::class)->createNotification($troubleTicket, $troubleTicket->assignee);
            $entityManager->persist($notification);
            $entityManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            JiraItemDataProvider::class,
            CommentRequestManager::class,
            EntityManagerInterface::class,
            TroubleTicketNotifier::class,
            Security::class,
            EmailChangeSetFactory::class,
            WorkflowStatusUpdater::class,
            TroubleTicketManager::class,
            IriConverterInterface::class,
            TroubleTicketAssignedNotificationFactory::class,
            TroubleTicketClosedNotificationFactory::class,
            ResourceMetadataCollectionFactoryInterface::class,
            RecipientsFinder::class,
        ];
    }

    private function createNotification(TroubleTicket $troubleTicket, string $notificationFactoryClass, $multipleRecipients = false): void
    {
        $recipientsFinder = $this->serviceLocator->get(RecipientsFinder::class);
        $notificationFactory = $this->serviceLocator->get($notificationFactoryClass);
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        $recipients = $multipleRecipients ? [...$recipientsFinder->findTos($troubleTicket), ...$recipientsFinder->findCcs($troubleTicket)] : [];

        if ($multipleRecipients) {
            /** @var People $recipient */
            foreach ($recipients as $recipient) {
                if ($this->serviceLocator->get(Security::class)->getUser()->getUserIdentifier() === $recipient->getUserIdentifier()) {
                    continue;
                }
                $notification = $notificationFactory->createNotification($troubleTicket, $recipient);
                $entityManager->persist($notification);
            }
        } else {
            $notification = $notificationFactory->createNotification($troubleTicket, $troubleTicket->assignee);
            $entityManager->persist($notification);
        }

        $entityManager->flush();
    }
}
