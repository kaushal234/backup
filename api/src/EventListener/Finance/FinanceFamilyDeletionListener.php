<?php

declare(strict_types=1);

namespace App\EventListener\Finance;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Finance\FinanceFamily;
use App\Entity\Sales\Product;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class FinanceFamilyDeletionListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onPreDelete(ViewEvent $event)
    {
        $financeFamily = $event->getControllerResult();

        if (!$financeFamily instanceof FinanceFamily) {
            return;
        }

        if (!$event->getRequest()->isMethod(Request::METHOD_DELETE)) {
            return;
        }

        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $productRepository = $em->getRepository(Product::class);
        $products = $productRepository->findBy(['financeFamily' => $financeFamily]);

        foreach ($products as $product) {
            $product->setFinanceFamily(null);
            $em->persist($product);
        }

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
