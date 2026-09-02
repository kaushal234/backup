<?php

declare(strict_types=1);

namespace App\EventListener\Sales\Order;

use App\Entity\Sales\Order;
use LegacyBundle\Manager\SalesOrderLineManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class OrderWorkflowGuardListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function guardInProgress(GuardEvent $event)
    {
        $order = $event->getSubject();
        if (!$order instanceof Order || null === $order->getLegacyId()) {
            return;
        }

        $salesOrderLines = $this->serviceLocator->get(SalesOrderLineManager::class)->getSalesOrderLines($order);
        if ([] === $salesOrderLines) {
            $event->addTransitionBlocker(new TransitionBlocker('There are no SOLs linked to this Sales Order.', TransitionBlocker::UNKNOWN));

            return;
        }
        $delinquents = array_filter($salesOrderLines, static fn (array $salesOrder) => '0000-00-00 00:00:00' === ($salesOrder['dt_create_factory'] ?? '0000-00-00 00:00:00'));

        if ([] !== $delinquents) {
            $event->addTransitionBlocker(new TransitionBlocker(\sprintf("The following linked SOLs haven't reached the factory: %s", implode(', ', array_column($delinquents, 'id'))), TransitionBlocker::UNKNOWN));
        }
    }

    public function guardClosed(GuardEvent $event)
    {
        $order = $event->getSubject();
        if (!$order instanceof Order || null === $order->getLegacyId()) {
            return;
        }

        if ($this->serviceLocator->get(SalesOrderLineManager::class)->isPreventingOrderClosing($order)) {
            $event->addTransitionBlocker(new TransitionBlocker('Some linked SOLs are not closed.', TransitionBlocker::UNKNOWN));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.sales_order.guard.to_in_progress' => ['guardInProgress'],
            'workflow.sales_order.guard.to_closed' => ['guardClosed'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            SalesOrderLineManager::class,
        ];
    }
}
