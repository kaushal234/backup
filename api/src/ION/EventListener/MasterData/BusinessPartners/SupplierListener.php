<?php

declare(strict_types=1);

namespace App\ION\EventListener\MasterData\BusinessPartners;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\Location;
use App\Entity\SupplierEntityInterface;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerManager;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SupplierListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Must run before validation (PRE_VALIDATE), not PRE_WRITE: entities with a
            // validation group requiring supplierName (e.g. FirstArticleQualification's
            // "buyer" group) would otherwise always fail since supplierName is only
            // derived from supplierNumber here, after validation would have run.
            KernelEvents::VIEW => [['setSupplierName', EventPriorities::PRE_VALIDATE]],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [BusinessPartnerManager::class];
    }

    public function setSupplierName(ViewEvent $event): void
    {
        $supplierEntity = $event->getControllerResult();
        $request = $event->getRequest();
        if (!$supplierEntity instanceof SupplierEntityInterface || !\in_array($request->getMethod(), [Request::METHOD_PUT, Request::METHOD_POST], true)) {
            return;
        }

        if (Location::TLD_ERP_SOFTWARE !== $supplierEntity->getLocation()?->getErpSoftware()) {
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

        /** @var BusinessPartner|null $erpSupplier */
        $erpSupplier = $this->serviceLocator->get(BusinessPartnerManager::class)->findSupplier($supplierEntity->getSupplierNumber());

        if (null !== $erpSupplier) {
            $supplierEntity->setSupplierName($erpSupplier->name);
        }
    }
}
