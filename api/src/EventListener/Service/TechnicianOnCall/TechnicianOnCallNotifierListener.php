<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\CustomerServiceRecord\TechnicianOnCallCustomerServiceRecord;
use App\Entity\Service\TechnicianOnCall;
use App\Factory\EmailChangeSetFactory;
use App\Manager\Service\BlackCat;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallNotifier;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsDoctrineListener(event: Events::preFlush)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onCreation', priority: EventPriorities::POST_WRITE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onUpdate', priority: EventPriorities::POST_WRITE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onSolved', priority: EventPriorities::POST_WRITE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onNewComment', priority: EventPriorities::POST_WRITE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onStatusChangeFromPendingToInProgress', priority: EventPriorities::POST_WRITE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onCustomerServiceRecordCreation', priority: EventPriorities::POST_WRITE)]
class TechnicianOnCallNotifierListener extends AbstractTechnicianOnCallListener
{
    private array $itemChangeset = [];

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function onCreation(ViewEvent $event): void
    {
        $user = $this->container->get(Security::class)->getUser();

        if (!$user instanceof ExtranetUser && !$user instanceof People) {
            return;
        }

        if (!$this->isTechnicianOnCallCreation($event)) {
            return;
        }

        $toc = $event->getControllerResult();

        $notifier = $this->container->get(TechnicianOnCallNotifier::class);

        if ($user instanceof ExtranetUser) {
            $notifier->sendEmails([
                TechnicianOnCallMailSubject::TOC_CREATED_BY_CUSTOMER,
            ], $toc);

            return;
        }

        $emailsToSend = [
            TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_INTERNAL,
        ];

        if (null !== $toc->equipmentRecord && $this->container->get(BlackCat::class)->isBlackCat($toc->equipmentRecord)) {
            $emailsToSend[] = TechnicianOnCallMailSubject::TOC_BLACK_CAT;
        }

        if (!$toc->isConfidential() && null !== $toc->getMainContact()) {
            $emailsToSend[] = TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_EXTERNAL;
        }

        $notifier->sendEmails($emailsToSend, $toc);
    }

    public function preFlush(PreFlushEventArgs $event): void
    {
        $request = $this->container->get(RequestStack::class)->getCurrentRequest();

        if (null === $request) {
            return;
        }

        if (
            'technician_on_call_edit' !== $request->attributes->get('_route')
            && 'technician_on_call_status' !== $request->attributes->get('_route')
        ) {
            return;
        }

        $em = $event->getObjectManager();
        if (!$em instanceof EntityManagerInterface) {
            return;
        }

        $identityMap = $em->getUnitOfWork()->getIdentityMap();
        $tocEntities = $identityMap[TechnicianOnCall::class] ?? [];
        $toc = reset($tocEntities);

        if (!$toc instanceof TechnicianOnCall) {
            return;
        }

        $this->itemChangeset = $this->container->get(EmailChangeSetFactory::class)->createChangeSetForEmail($toc, ['token', 'defectiveParts'], 'Y-m-d');
    }

    public function onUpdate(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallRoute($event, 'technician_on_call_edit', 'technician_on_call_status')) {
            return;
        }

        $toc = $event->getControllerResult();
        $notifier = $this->container->get(TechnicianOnCallNotifier::class);
        $context = ['changeSet' => $this->itemChangeset];

        $notifier->sendEmails([TechnicianOnCallMailSubject::TOC_UPDATED], $toc, $context);
    }

    public function onSolved(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallRoute($event, 'technician_on_call_edit', 'technician_on_call_status')) {
            return;
        }

        $toc = $event->getControllerResult();

        /** @var TechnicianOnCall $previousToc */
        $previousToc = $event->getRequest()->attributes->get('previous_data');

        if (TechnicianOnCall::SOLVED !== $toc->status || TechnicianOnCall::SOLVED === $previousToc->status) {
            return;
        }

        $notifier = $this->container->get(TechnicianOnCallNotifier::class);

        if (!$toc->isConfidential()) {
            $notifier->sendEmails([TechnicianOnCallMailSubject::TOC_SOLVED_EXTERNAL], $toc);
        }
    }

    public function onStatusChangeFromPendingToInProgress(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallRoute($event, 'technician_on_call_edit', 'technician_on_call_status')) {
            return;
        }

        /** @var TechnicianOnCall $toc */
        $toc = $event->getControllerResult();

        /** @var TechnicianOnCall $previousToc */
        $previousToc = $event->getRequest()->attributes->get('previous_data');

        if (TechnicianOnCall::PENDING !== $previousToc->status || TechnicianOnCall::IN_PROGRESS !== $toc->status) {
            return;
        }

        $notifier = $this->container->get(TechnicianOnCallNotifier::class);

        $notifier->sendEmails([
            TechnicianOnCallMailSubject::TOC_PENDING_TO_IN_PROGRESS_EXTERNAL,
            TechnicianOnCallMailSubject::TOC_PENDING_TO_IN_PROGRESS_INTERNAL,
        ], $toc, []
        );
    }

    public function onNewComment(ViewEvent $event): void
    {
        $route = $event->getRequest()->attributes->get('_route');

        /** @var Comment $comment */
        $comment = $event->getControllerResult();

        if (($comment->metadata['notifications'] ?? null) === false) {
            return;
        }

        if ('api_comments_post_collection' !== $route || !$comment instanceof Comment) {
            return;
        }

        $technicianOnCall = $this->container->get(IriConverterInterface::class)->getResourceFromIri($comment->getResource());

        if (!$technicianOnCall instanceof TechnicianOnCall) {
            return;
        }

        if ($this->container->get(TechnicianOnCallCommentService::class)->isFactoryFlagChangeComment($comment)) {
            $this->container->get(TechnicianOnCallNotifier::class)->sendEmails(
                [TechnicianOnCallMailSubject::TOC_FACTORY_FLAG],
                $technicianOnCall,
                [
                    'userName' => $this->container->get(Security::class)->getUser()->getUsername(),
                    'comment' => $comment,
                ]
            );

            return;
        }

        $notifier = $this->container->get(TechnicianOnCallNotifier::class);

        $user = $this->container->get(Security::class)->getUser();

        $emailsToSend = [];

        if ($user instanceof ExtranetUser) {
            $emailsToSend[] = TechnicianOnCallMailSubject::TOC_NEW_COMMENT_BY_CUST;
        }

        if ($user instanceof People) {
            $emailsToSend[] = TechnicianOnCallMailSubject::TOC_NEW_COMMENT_INTERNAL;
        }

        if ($user instanceof People && $comment->isPublic() && !$technicianOnCall->isConfidential()) {
            $emailsToSend[] = TechnicianOnCallMailSubject::TOC_NEW_COMMENT_EXTERNAL;
        }

        $context = [
            'user' => $user,
            'comment' => $comment,
        ];

        $notifier->sendEmails($emailsToSend, $technicianOnCall, $context);
    }

    public function onCustomerServiceRecordCreation(ViewEvent $event): void
    {
        $customerServiceRecord = $event->getControllerResult();

        if (!$customerServiceRecord instanceof TechnicianOnCallCustomerServiceRecord) {
            return;
        }

        $request = $event->getRequest();

        if (null !== $request->attributes->get('previous_data')) {
            return;
        }

        if (!$customerServiceRecord->hasOpenIntervention()) {
            return;
        }

        $notifier = $this->container->get(TechnicianOnCallNotifier::class);
        $notifier->sendEmails([TechnicianOnCallMailSubject::TOC_CSR_CREATED], $customerServiceRecord->getTechnicianOnCall(), ['customerServiceRecord' => $customerServiceRecord]);
    }

    public static function getSubscribedServices(): array
    {
        return [
            HttpKernelInterface::class,
            RequestStack::class,
            Security::class,
            TranslatorInterface::class,
            TechnicianOnCallNotifier::class,
            EmailChangeSetFactory::class,
            IriConverterInterface::class,
            EntityManagerInterface::class,
            TechnicianOnCallCommentService::class,
            BlackCat::class,
        ];
    }
}
