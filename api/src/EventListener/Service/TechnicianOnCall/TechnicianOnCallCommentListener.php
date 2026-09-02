<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCallCommentDiscriminator;
use App\Http\DeeplClient;
use App\Request\SubRequestManager;
use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener(event: KernelEvents::VIEW, method: 'onCommentFactoryFlag', priority: EventPriorities::POST_VALIDATE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onCommentTranslateMessage', priority: EventPriorities::PRE_WRITE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onCommentChangeUpdatedAt', priority: EventPriorities::PRE_WRITE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onCommentChangeStatus', priority: EventPriorities::PRE_WRITE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onCommentForSubscription', priority: EventPriorities::PRE_WRITE)]
#[AsEventListener(event: KernelEvents::REQUEST, method: 'commentFactoryFlagSecurity', priority: EventPriorities::POST_DESERIALIZE)]
readonly class TechnicianOnCallCommentListener implements ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            HttpKernelInterface::class,
            IriConverterInterface::class,
            SubRequestManager::class,
            ValidatorInterface::class,
            DeeplClient::class,
            EntityManagerInterface::class,
            Security::class,
            TranslatorInterface::class,
            TechnicianOnCallCommentService::class,
            WorkflowStatusUpdater::class,
        ];
    }

    public function isTechnicianOnCallCommentEvent(ViewEvent $event): bool
    {
        $comment = $event->getControllerResult();
        if (!$comment instanceof Comment) {
            return false;
        }

        $resource = $this->container->get(IriConverterInterface::class)->getResourceFromIri($comment->getResource());
        if (!$resource instanceof TechnicianOnCall) {
            return false;
        }

        return true;
    }

    public function onCommentFactoryFlag(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallCommentEvent($event)) {
            return;
        }

        /** @var Comment $comment */
        $comment = $event->getControllerResult();

        /** @var IriConverterInterface $iriConverter */
        $iriConverter = $this->container->get(IriConverterInterface::class);
        $technicianOnCall = $iriConverter->getResourceFromIri($comment->getResource());

        if (!$this->container->get(TechnicianOnCallCommentService::class)->isFactoryFlagChangeComment($comment)) {
            return;
        }

        // A Comment changing the Factory Flag status of the TOC should always be private to be unavailable on extranet.
        $comment->setPublic(false);
        $comment->metadata['notifications'] = true;

        $technicianOnCall->factoryFlag = TechnicianOnCallCommentDiscriminator::OPEN_FACTORY_FLAG->name === $comment->metadata['factoryFlag'];

        if (!$event->isMainRequest()) {
            return;
        }

        /** @var ValidatorInterface $validator */
        $validator = $this->container->get(ValidatorInterface::class);
        $validator->validate($technicianOnCall, ['groups' => 'factory_flag']);
    }

    public function onCommentTranslateMessage(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallCommentEvent($event)) {
            return;
        }

        $comment = $event->getControllerResult();
        $technicianOnCall = $this->container->get(IriConverterInterface::class)->getResourceFromIri($comment->getResource());

        /** @var DeeplClient $deeplClient */
        $deeplClient = $this->container->get(DeeplClient::class);

        $translatedMessage = $deeplClient->getTranslation(
            baseText: $comment->getMessage(),
            class: TechnicianOnCall::class,
            itemId: $technicianOnCall->getId(),
            returnOnlyTranslatedMessage: true,
            createLog: false,
        );

        $comment->metadata['translation'] = $translatedMessage === $comment->getMessage() ? null : $translatedMessage;
    }

    public function onCommentChangeUpdatedAt(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallCommentEvent($event)) {
            return;
        }
        $comment = $event->getControllerResult();
        $technicianOnCall = $this->container->get(IriConverterInterface::class)->getResourceFromIri($comment->getResource());

        $technicianOnCall->updatedAt = new \DateTime();
    }

    public function onCommentChangeStatus(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallCommentEvent($event)) {
            return;
        }

        /** @var Security $security */
        $security = $this->container->get(Security::class);
        $user = $security->getUser();

        if (!$user instanceof People) {
            return;
        }

        $comment = $event->getControllerResult();
        $technicianOnCall = $this->container->get(IriConverterInterface::class)->getResourceFromIri($comment->getResource());

        if (TechnicianOnCall::PENDING !== $technicianOnCall->getStatus()) {
            return;
        }

        /** @var WorkflowStatusUpdater $workflowStatus */
        $workflowStatus = $this->container->get(WorkflowStatusUpdater::class);
        $workflowStatus->applyStatus($technicianOnCall, TechnicianOnCall::IN_PROGRESS);
    }

    public function onCommentForSubscription(ViewEvent $event): void
    {
        /** @var Comment $comment */
        $comment = $event->getControllerResult();
        if (!isset($comment->metadata['subscription']) || !$this->isTechnicianOnCallCommentEvent($event)) {
            return;
        }

        $comment->metadata['notifications'] = false;
        $comment->setPublic(false);
    }

    public function commentFactoryFlagSecurity(RequestEvent $event): void
    {
        $comment = $event->getRequest()->attributes->get('data');
        $operation = $event->getRequest()->attributes->get('_api_operation');

        if (!$operation instanceof Post || !$comment instanceof Comment || !isset($comment->metadata['factoryFlag'])) {
            return;
        }

        if (!$event->isMainRequest()) {
            return;
        }

        /** @var Security $security */
        $security = $this->container->get(Security::class);

        /** @var TranslatorInterface $translator */
        $translator = $this->container->get(TranslatorInterface::class);

        if (TechnicianOnCallCommentDiscriminator::OPEN_FACTORY_FLAG->name === $comment->metadata['factoryFlag'] && !$security->isGranted('FEATURE_TECHNICIAN_ON_CALL_OPEN_FACTORY_FLAG')) {
            throw new AccessDeniedHttpException($translator->trans('toc.messages.security.factory_flag_open', [], 'technician_on_call'));
        }

        if (TechnicianOnCallCommentDiscriminator::CLOSE_FACTORY_FLAG->name === $comment->metadata['factoryFlag'] && !$security->isGranted('FEATURE_TECHNICIAN_ON_CALL_CLOSE_FACTORY_FLAG')) {
            throw new AccessDeniedHttpException($translator->trans('toc.messages.security.factory_flag_close', [], 'technician_on_call'));
        }
    }
}
