<?php

declare(strict_types=1);

namespace App\EventListener\Service;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Workflow\Handler\ChainWorkflowHandler;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class CustomerServiceRecordAssignmentListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    final public const FEATURE_CUSTOMER_SERVICE_RECORD_PLANNER = 'FEATURE_CUSTOMER_SERVICE_RECORD_PLANNER';

    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['assignment', EventPriorities::PRE_VALIDATE],
            ],
        ];
    }

    public function assignment(ViewEvent $event): void
    {
        $customerServiceRecord = $event->getControllerResult();

        if (!$customerServiceRecord instanceof AbstractCustomerServiceRecord) {
            return;
        }

        $request = $event->getRequest();

        if (!\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            return;
        }

        /** @var AbstractCustomerServiceRecord $previousData */
        $previousData = $request->attributes->get('previous_data');

        // Check if data has changed during planning update
        if (null !== $previousData
            && $previousData->leader === $customerServiceRecord->leader
            && $previousData->plannedAt === $customerServiceRecord->plannedAt
            && $previousData->getStatus() === $customerServiceRecord->getStatus()
        ) {
            return;
        }

        if (!$this->serviceLocator->get(Security::class)->isGranted(self::FEATURE_CUSTOMER_SERVICE_RECORD_PLANNER)) {
            throw new AccessDeniedException();
        }

        $this->serviceLocator->get(ChainWorkflowHandler::class)->handle($customerServiceRecord, $previousData);
    }

    public static function getSubscribedServices(): array
    {
        return [
            ChainWorkflowHandler::class,
            Security::class,
        ];
    }
}
