<?php

declare(strict_types=1);

namespace App\EventListener\Quality\SupplierCorrectiveActionRequest;

use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Notifier\Quality\SupplierCorrectiveActionRequest\SupplierCorrectiveActionRequestNotifier;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SupplierCorrectiveActionRequestWorkflowCompletedListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.supplier_corrective_action_request.completed.to_validation' => ['onStatusChange'],
            'workflow.supplier_corrective_action_request.completed.to_commercial_agreement' => ['onStatusChange'],
            'workflow.supplier_corrective_action_request.completed.to_closed' => ['onStatusChange'],
        ];
    }

    public function onStatusChange(CompletedEvent $event)
    {
        $supplierCorrectiveActionRequest = $event->getSubject();

        if (!$supplierCorrectiveActionRequest instanceof SupplierCorrectiveActionRequest) {
            return;
        }

        $this->serviceLocator->get(SupplierCorrectiveActionRequestNotifier::class)->sendStatus($supplierCorrectiveActionRequest, ['status' => mb_strtolower(preg_replace('/\s+/', '_', $supplierCorrectiveActionRequest->getStatus()))]);
    }

    public static function getSubscribedServices(): array
    {
        return [
            SupplierCorrectiveActionRequestNotifier::class,
        ];
    }
}
