<?php

declare(strict_types=1);

namespace LegacyBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContext;

class RequestContextListener implements EventSubscriberInterface
{
    private readonly RequestContext $context;

    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        // Hack for setting the module even when m is defined using POST data and not GET
        if ($request->isMethod(Request::METHOD_POST) && [] !== ($m = ($request->request->all()['m'] ?? [])) && [] === ($request->query->all()['m'] ?? [])) {
            $request->query->set('m', $m);
        }
        $this->context->fromRequest($event->getRequest());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 34],
        ];
    }
}
