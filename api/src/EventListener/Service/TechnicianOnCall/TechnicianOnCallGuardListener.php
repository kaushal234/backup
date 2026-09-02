<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use App\Entity\Service\TechnicianOnCall;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallGuardListener implements ServiceSubscriberInterface, EventSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.technician_on_call.guard.to_closed' => ['guardClosed'],
            'workflow.technician_on_call.guard.to_in_progress' => ['toInProgress'],
            'workflow.technician_on_call.guard.to_solved' => [
                ['guardClosed'],
                ['guardFactorySupport'],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            TranslatorInterface::class,
            Security::class,
        ];
    }

    public function guardClosed(GuardEvent $event): void
    {
        $technicianOnCall = $event->getSubject();

        if (!$technicianOnCall instanceof TechnicianOnCall) {
            return;
        }

        if ($technicianOnCall->customerServiceRecords->isEmpty()) {
            return;
        }

        $customerServiceRecordClosed = true;

        foreach ($technicianOnCall->customerServiceRecords as $customerServiceRecord) {
            if ($customerServiceRecord->isClosed()) {
                continue;
            }

            $customerServiceRecordClosed = false;
            break;
        }

        if ($customerServiceRecordClosed) {
            return;
        }

        /** @var TranslatorInterface $translator */
        $translator = $this->container->get(TranslatorInterface::class);
        $message = $translator->trans('toc.transition.guard.to_closed', ['%status%' => $technicianOnCall->getStatus()], 'technician_on_call');
        $event->setBlocked(true, $message);
    }

    public function guardFactorySupport(GuardEvent $event): void
    {
        $technicianOnCall = $event->getSubject();

        if (!$technicianOnCall instanceof TechnicianOnCall) {
            return;
        }

        if (!$technicianOnCall->factoryFlag) {
            return;
        }

        /** @var TranslatorInterface $translator */
        $translator = $this->container->get(TranslatorInterface::class);
        $message = $translator->trans('toc.transition.guard.solved_with_factory_support', [], 'technician_on_call');
        $event->setBlocked(true, $message);
    }

    public function toInProgress(GuardEvent $event): void
    {
        $technicianOnCall = $event->getSubject();

        if (!$technicianOnCall instanceof TechnicianOnCall) {
            return;
        }

        if (TechnicianOnCall::CLOSED !== $technicianOnCall->getStatus()) {
            return;
        }

        /** @var Security $security */
        $security = $this->container->get(Security::class);

        if ($security->isGranted('FEATURE_REOPEN_TECHNICIAN_ON_CALL')) {
            return;
        }

        /** @var TranslatorInterface $translator */
        $translator = $this->container->get(TranslatorInterface::class);
        $message = $translator->trans('toc.transition.guard.closed_to_in_progress', [], 'technician_on_call');
        $event->setBlocked(true, $message);
    }
}
