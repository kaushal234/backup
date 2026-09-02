<?php

declare(strict_types=1);

namespace App\EventListener\Sales\Customer;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Sales\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class CustomerDeletionListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onPreDelete(ViewEvent $event)
    {
        /** @var Customer $customer */
        $customer = $event->getControllerResult();

        if (!$customer instanceof Customer || !$event->getRequest()->isMethod(Request::METHOD_DELETE)) {
            return;
        }

        $customer->setName(\sprintf('%s_DELETED', $customer->getName()));

        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $em->persist($customer);
        $em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onPreDelete', EventPriorities::PRE_WRITE],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
        ];
    }
}
