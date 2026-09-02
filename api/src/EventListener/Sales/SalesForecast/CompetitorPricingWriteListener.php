<?php

declare(strict_types=1);

namespace App\EventListener\Sales\SalesForecast;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Sales\CompetitorPricing;
use App\Request\Sales\SalesForecastRequestManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class CompetitorPricingWriteListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['afterCompetitorPricingCreation', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function afterCompetitorPricingCreation(ViewEvent $event)
    {
        $result = $event->getControllerResult();

        if (!$result instanceof CompetitorPricing || !$event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        if ($result->isLast() && null !== ($forecastClosure = $result->getForecastClosure())) {
            $this->serviceLocator->get(SalesForecastRequestManager::class)->notify($forecastClosure->getSalesForecast());
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            SalesForecastRequestManager::class,
        ];
    }
}
