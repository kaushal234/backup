<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use App\Doctrine\Change;
use App\Entity\Service\TechnicianOnCall;
use App\Event\EntityChangeEvent;
use App\Manager\Service\TechnicianOnCallLogManager;
use App\Request\Activity\CommentRequestManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class TechnicianOnCallLogToCommentListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $serviceLocator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityChangeEvent::class => [
                ['creation'],
                ['propertiesChanges'],
            ],
        ];
    }

    public function creation(EntityChangeEvent $event): void
    {
        $request = $this->serviceLocator->get(RequestStack::class)->getCurrentRequest();
        if (!$request) {
            return;
        }

        $change = $event->getChange();

        if (!$change->getEntity() instanceof TechnicianOnCall) {
            return;
        }

        if (Change::ACTION_CREATE !== $change->getAction()) {
            return;
        }

        /** @var TechnicianOnCallLogManager $logManager */
        $logManager = $this->serviceLocator->get(TechnicianOnCallLogManager::class);
        $comment = $logManager->creationComment($change);

        /** @var CommentRequestManager $commentManager */
        $commentManager = $this->serviceLocator->get(CommentRequestManager::class);
        $commentManager->insertComment(
            $change->getEntity(),
            $comment,
            null,
            ['notifications' => false],
            TechnicianOnCall::MODULE_NAME
        );
    }

    public function propertiesChanges(EntityChangeEvent $event): void
    {
        $request = $this->serviceLocator->get(RequestStack::class)->getCurrentRequest();
        if (!$request) {
            return;
        }

        $change = $event->getChange();

        if (!$change->getEntity() instanceof TechnicianOnCall) {
            return;
        }

        if (Change::ACTION_UPDATE !== $change->getAction()) {
            return;
        }

        /** @var TechnicianOnCallLogManager $logManager */
        $logManager = $this->serviceLocator->get(TechnicianOnCallLogManager::class);
        $comment = $logManager->logToComment($change);

        if (!$comment) {
            return;
        }

        /** @var TranslatorInterface $translator */
        $translator = $this->serviceLocator->get(TranslatorInterface::class);
        $prepend = $translator->trans('toc.comment.change', [], 'technician_on_call');

        $comment = \sprintf("%s \n %s", $prepend, $comment);

        /** @var CommentRequestManager $commentManager */
        $commentManager = $this->serviceLocator->get(CommentRequestManager::class);
        $commentManager->insertComment(
            $change->getEntity(),
            $comment,
            null,
            ['notifications' => false],
            TechnicianOnCall::MODULE_NAME,
            false,
        );
    }

    public static function getSubscribedServices(): array
    {
        return [
            CommentRequestManager::class,
            TechnicianOnCallLogManager::class,
            TranslatorInterface::class,
            RequestStack::class,
        ];
    }
}
