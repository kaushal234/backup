<?php

declare(strict_types=1);

namespace App\EventListener\PowerBI;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\PowerBI\Report;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;

class UuidListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['postValidate', EventPriorities::POST_VALIDATE],
        ];
    }

    /**
     * Create a Uuid object after Uuid validation.
     * This will prevent error from creating a Uuid object.
     */
    public function postValidate(ViewEvent $event): void
    {
        /** @var Report $report */
        $report = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$report instanceof Report || !\in_array($method, [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            return;
        }

        $report->setPowerBiUuidObject(new Uuid($report->powerBiUuid));
    }
}
