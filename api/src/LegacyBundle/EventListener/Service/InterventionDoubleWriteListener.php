<?php

declare(strict_types=1);

namespace LegacyBundle\EventListener\Service;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use LegacyBundle\Event\UpdateEvent;
use LegacyBundle\Manager\CustomerServiceRecordManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class InterventionDoubleWriteListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function onInterventionUpdate(UpdateEvent $event): void
    {
        $intervention = $event->getObject();

        if (!$intervention instanceof Intervention || $intervention !== $intervention->customerServiceRecord->getInterventions()->last()) {
            return;
        }

        $customerServiceRecordManager = $this->container->get(CustomerServiceRecordManager::class);
        $customerServiceRecordManager->doubleWriteFromIntervention($intervention);
    }

    public function onCustomerServiceRecordUpdate(ViewEvent $event): void
    {
        $customerServiceRecord = $event->getControllerResult();
        $request = $event->getRequest();

        if (
            !$customerServiceRecord instanceof AbstractCustomerServiceRecord
            || !$request->isMethod(Request::METHOD_PUT)
            || $customerServiceRecord->getInterventions()->isEmpty()
        ) {
            return;
        }

        $customerServiceRecordManager = $this->container->get(CustomerServiceRecordManager::class);
        $customerServiceRecordManager->doubleWriteFromIntervention($customerServiceRecord->getInterventions()->last());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UpdateEvent::class => 'onInterventionUpdate',
            KernelEvents::VIEW => [
                ['onCustomerServiceRecordUpdate', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            CustomerServiceRecordManager::class,
        ];
    }
}
