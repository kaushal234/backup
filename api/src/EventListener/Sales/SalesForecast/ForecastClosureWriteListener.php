<?php

declare(strict_types=1);

namespace App\EventListener\Sales\SalesForecast;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Sales\ForecastClosure;
use App\Entity\Sales\SalesForecast;
use App\Request\Activity\CommentRequestManager;
use App\Request\Sales\SalesForecastRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ForecastClosureWriteListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['beforeForecastClosureDeletion', EventPriorities::PRE_WRITE],
                ['afterForecastClosureCreation', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function afterForecastClosureCreation(ViewEvent $event)
    {
        $result = $event->getControllerResult();

        if (!$result instanceof ForecastClosure || !$event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        $salesForecastStatus = $this->getSalesForecastStatusFromForecastClosure($result);

        $message = \sprintf('Closing comment: %s', $result->getComment());

        $commentManager = $this->serviceLocator->get(CommentRequestManager::class);
        $commentManager->insertComment($result, $result->getComment());

        $commentManager->insertComment($result->getSalesForecast(), $message);

        if (SalesForecast::LOST === $result->getStatus()) {
            $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
            $salesForecast = $result->getSalesForecast();

            $salesForecast->setQuantity($result->getOrderedQuantity());
            $entityManager->persist($salesForecast);
            $entityManager->flush();
        }

        if (SalesForecast::PARTIAL === $salesForecastStatus && 2 !== $result->getSalesForecast()->getForecastClosures()->count()) {
            return;
        }

        $this->serviceLocator->get(SalesForecastRequestManager::class)->updateStatus($result->getSalesForecast(), $salesForecastStatus);
    }

    public function beforeForecastClosureDeletion(ViewEvent $event)
    {
        $forecastClosure = $event->getRequest()->attributes->get('data');

        if (!$forecastClosure instanceof ForecastClosure || !$event->getRequest()->isMethod(Request::METHOD_DELETE)) {
            return;
        }

        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        foreach ($forecastClosure->getCompetitorPricings() as $competitorPricing) {
            $competitorPricing->setForecastClosure(null);
            $em->persist($competitorPricing);
        }

        $em->flush();
    }

    public static function getSubscribedServices(): array
    {
        return [
            CommentRequestManager::class,
            SalesForecastRequestManager::class,
            EntityManagerInterface::class,
        ];
    }

    private function getSalesForecastStatusFromForecastClosure(ForecastClosure $forecastClosure)
    {
        return 0 === mb_strpos($forecastClosure->getStatus(), SalesForecast::PARTIAL) ? SalesForecast::PARTIAL : $forecastClosure->getStatus();
    }
}
