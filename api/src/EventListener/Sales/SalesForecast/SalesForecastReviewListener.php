<?php

declare(strict_types=1);

namespace App\EventListener\Sales\SalesForecast;

use App\Entity\Sales\ForecastClosure;
use App\Entity\Sales\SalesForecast;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SalesForecastReviewListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function guardReview(GuardEvent $event)
    {
        $security = $this->serviceLocator->get(Security::class);
        if ($security->isGranted('MOO_SFR') || $security->isGranted('FEATURE_SALES_FORECAST_FORCE_STATUS')) {
            return;
        }

        /** @var SalesForecast $salesForecast */
        $salesForecast = $event->getSubject();
        $forecastClosures = $salesForecast->getForecastClosures();

        if (!\in_array(current($event->getTransition()->getTos()), [SalesForecast::LOST, SalesForecast::ORDERED, SalesForecast::PARTIAL, SalesForecast::ORDER_CANCELLED], true)) {
            return;
        }

        if ($forecastClosures->isEmpty()) {
            $event->setBlocked(true);

            return;
        }

        switch ($event->getTransition()->getName()) {
            case 'to_ordered':
            case 'to_lost':
                $to = current($event->getTransition()->getTos());

                /** @var ForecastClosure $lastClosure */
                $lastClosure = $forecastClosures->first();

                if ($lastClosure->getStatus() === $to) {
                    return;
                }
                break;
            case 'to_partial':
                $statuses = (array) array_reduce($forecastClosures->toArray(), static function (array $memo, ForecastClosure $forecastClosure) {
                    $memo[] = $forecastClosure->getStatus();

                    return $memo;
                }, []);

                if (2 === \count($statuses) && \in_array(ForecastClosure::PARTIAL_LOST, $statuses, true) && \in_array(ForecastClosure::PARTIAL_ORDERED, $statuses, true)) {
                    return;
                }
                break;
        }

        $event->setBlocked(true);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.sales_forecast.guard.to_ordered' => ['guardReview'],
            'workflow.sales_forecast.guard.to_lost' => ['guardReview'],
            'workflow.sales_forecast.guard.to_partial' => ['guardReview'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            Security::class,
        ];
    }
}
