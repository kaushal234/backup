<?php

declare(strict_types=1);

namespace App\SageParts\P21\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\Location;
use App\Entity\SupplierEntityInterface;
use App\SageParts\P21\Manager\SupplierManager;
use App\SageParts\P21\Resources\Supplier;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SupplierListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [['setSupplierName', EventPriorities::PRE_WRITE]],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [SupplierManager::class];
    }

    public function setSupplierName(ViewEvent $event): void
    {
        $supplierEntity = $event->getControllerResult();
        $request = $event->getRequest();
        if (!$supplierEntity instanceof SupplierEntityInterface || !\in_array($request->getMethod(), [Request::METHOD_PUT, Request::METHOD_POST], true)) {
            return;
        }

        if (Location::SAGEPARTS_ERP_SOFTWARE !== $supplierEntity->getLocation()?->getErpSoftware()) {
            return;
        }

        /** @var SupplierEntityInterface|null $previous */
        $previous = $request->attributes->get('previous_data');
        if (Request::METHOD_PUT === $request->getMethod() && null !== $previous && $previous->getSupplierNumber() === $supplierEntity->getSupplierNumber()) {
            return;
        }

        if (null === $supplierEntity->getSupplierNumber()) {
            return;
        }

        /** @var Supplier|null $erpSupplier */
        $erpSupplier = $this->serviceLocator->get(SupplierManager::class)->findSupplier($supplierEntity->getSupplierNumber());

        if (null !== $erpSupplier) {
            $supplierEntity->setSupplierName($erpSupplier->name);
        }
    }
}
