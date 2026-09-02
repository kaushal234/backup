<?php

declare(strict_types=1);

namespace App\ION\EventListener\Procurement;

use App\Entity\Purchasing\VendorUser;
use App\ION\Event\IONPreRequestEvent;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Resources\Procurement\RequestForQuotation;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class RequestForQuotationEventListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function preRequest(IONPreRequestEvent $event)
    {
        if (RequestForQuotation::class !== $event->getResourceClass()) {
            return;
        }

        $user = $this->serviceLocator->get(Security::class)->getUser();

        if (!$user instanceof VendorUser) {
            return;
        }

        $codes = [];
        /** @var BusinessPartner $businessPartner */
        foreach ($user->contact->getBusinessPartners() as $businessPartner) {
            $codes[] = $businessPartner->code;
        }

        $event->addParameter('bidder', implode(',', $codes));
    }

    public static function getSubscribedEvents(): array
    {
        return [IONPreRequestEvent::class => ['preRequest']];
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}
