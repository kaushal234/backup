<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use App\Entity\Service\TechnicianOnCall;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class AbstractTechnicianOnCallListener implements ServiceSubscriberInterface
{
    public static function getSubscribedServices(): array
    {
        return [];
    }

    public function isTechnicianOnCallCreation(ViewEvent $event): bool
    {
        $technicianOnCall = $event->getControllerResult();

        if (!$technicianOnCall instanceof TechnicianOnCall) {
            return false;
        }

        $request = $event->getRequest();

        if (Request::METHOD_POST !== $request->getMethod()) {
            return false;
        }

        return true;
    }

    public function isTechnicianOnCall(ViewEvent $event): bool
    {
        return $event->getControllerResult() instanceof TechnicianOnCall;
    }

    public function isTechnicianOnCallRoute(ViewEvent $event, string ...$routes): bool
    {
        if (!$event->getControllerResult() instanceof TechnicianOnCall) {
            return false;
        }

        return \in_array($event->getRequest()->attributes->get('_route'), $routes, true);
    }
}
