<?php

declare(strict_types=1);

namespace App\EventListener\Manufacturing;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Dto\Manufacturing\LeadTimeBatch;
use App\Notifier\Manufacturing\LeadTime\LeadTimeNotifier;
use App\Repository\Sales\SalesForecastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class LeadTimeUpdateListener implements EventSubscriberInterface, ServiceSubscriberInterface
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
                ['onLeadTimesUpdate', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onLeadTimesUpdate(ViewEvent $event)
    {
        $result = $event->getControllerResult();

        if (!$result instanceof LeadTimeBatch || Request::METHOD_POST !== $event->getRequest()->getMethod()) {
            return;
        }

        $this->serviceLocator->get(LeadTimeNotifier::class)->sendUpdateEmail(...$result->getPersistedLeadTimes());
    }

    public static function getSubscribedServices(): array
    {
        return [SalesForecastRepository::class, LeadTimeNotifier::class, EntityManagerInterface::class, NormalizerInterface::class];
    }
}
