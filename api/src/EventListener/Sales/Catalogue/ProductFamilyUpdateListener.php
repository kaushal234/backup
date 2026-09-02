<?php

declare(strict_types=1);

namespace App\EventListener\Sales\Catalogue;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Sales\ProductFamily;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ProductFamilyUpdateListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [['makeNonPublicWhenHidden', EventPriorities::PRE_WRITE]],
        ];
    }

    public function makeNonPublicWhenHidden(ViewEvent $event)
    {
        $family = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$family instanceof ProductFamily || !$request->isMethod(Request::METHOD_PUT) || !$family->isHidden()) {
            return;
        }

        $family->setPublicForTLD(false);
    }
}
