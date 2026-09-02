<?php

declare(strict_types=1);

namespace LegacyBundle\EventListener;

use LegacyBundle\Http\LegacyResourceNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class LegacyExceptionListener implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof LegacyResourceNotFoundException) {
            return;
        }

        $event->setThrowable(
            new NotFoundHttpException(
                $event->getThrowable()->getMessage(),
                $event->getThrowable()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
