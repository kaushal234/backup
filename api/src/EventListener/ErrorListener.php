<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[AsEventListener(event: 'kernel.exception', priority: -95)]
final class ErrorListener
{
    private bool $enabled = true;

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $exception = $event->getThrowable();

        if (!$exception instanceof HttpException) {
            return;
        }

        $message = new \ReflectionProperty(HttpException::class, 'message');

        switch ($exception->getStatusCode()) {
            case Response::HTTP_NOT_FOUND:
                $message->setValue($exception, 'Not Found');
                break;
            case Response::HTTP_FORBIDDEN:
                $message->setValue($exception, 'Access Denied');
                break;
            case Response::HTTP_BAD_REQUEST:
            case Response::HTTP_CONFLICT:
            case Response::HTTP_UNPROCESSABLE_ENTITY:
                // Let API Platform handle this case
                return;
            default:
                $message->setValue($exception, 'Internal Server Error');
        }

        $request = $event->getRequest();
        $request->attributes->set('_format', 'jsonld');

        $event->setThrowable($exception);
    }

    public function disable()
    {
        $this->enabled = false;
    }

    public function enable()
    {
        $this->enabled = true;
    }
}
