<?php

declare(strict_types=1);

namespace App\ION\EventListener\Procurement\Orders;

use App\Entity\Purchasing\VendorUser;
use App\ION\Event\IONPreNormalizeEvent;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class PurchaseOrderRequestEventListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function preRequest(IONPreNormalizeEvent $event)
    {
        if (PurchaseOrder::class !== $event->getResourceClass()) {
            return;
        }

        $user = $this->serviceLocator->get(Security::class)->getUser();

        if (!$user instanceof VendorUser) {
            return;
        }

        $codes = ['NO_BP_NO_RESULTS'];
        /** @var BusinessPartner $businessPartner */
        foreach ($user->contact->getBusinessPartners() as $businessPartner) {
            $codes[] = $businessPartner->code;
        }

        $event->addDataArea('buyFromSupplierCode', implode('|', $codes));
    }

    public static function getSubscribedEvents(): array
    {
        return [IONPreNormalizeEvent::class => ['preRequest']];
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}
