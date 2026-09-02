<?php

declare(strict_types=1);

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class SecurityTargetPathListener implements EventSubscriberInterface
{
    use TargetPathTrait;

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof AuthenticationException && !$exception instanceof AccessDeniedException) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->isMethodSafe()) {
            return;
        }

        $requestUri = $request->getUri();
        if (false !== mb_stripos($requestUri, 'sage-login')) {
            return;
        }

        $this->saveTargetPath($request->getSession(), 'main', $requestUri);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 1],
        ];
    }
}
