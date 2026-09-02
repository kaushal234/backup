<?php

declare(strict_types=1);

namespace ApiBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ClientExceptionListener implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $exception = $event->getThrowable();

        if ($exception instanceof ClientException) {
            switch ($exception->getCode()) {
                case Response::HTTP_NOT_FOUND:
                    $this->handleException(NotFoundHttpException::class, $event);
                    break;
                case Response::HTTP_UNPROCESSABLE_ENTITY:
                    $this->handleException(UnprocessableEntityHttpException::class, $event);
                    break;
                case Response::HTTP_FORBIDDEN:
                    throw new AccessDeniedException('You are not allowed to access this resource', $exception);
            }
        }
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

    private function handleException(string $exception, ExceptionEvent $event)
    {
        $event->setThrowable(
            new $exception(
                $event->getThrowable()->getMessage(),
                $event->getThrowable()
            )
        );
    }
}
