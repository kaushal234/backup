<?php

declare(strict_types=1);

namespace App\Client\EventListener;

use App\Client\Exception\SoapException;
use App\ION\Resources\SpartaExtensions\Times\TimeKeepingPostTransaction;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class SoapExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $timekeepingRequestLogger
    ) {
    }

    public function onSoapException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof SoapException) {
            return;
        }
        if (TimeKeepingPostTransaction::class === $exception->getTrace()[0]['args'][0]) {
            $this->timekeepingRequestLogger->error($exception->getMessage());
        }

        $errors = [];
        foreach ($exception->getMessages() as $message) {
            if ($message->isError()) {
                $errors[] = $message->text;
                if ($message->isNotFound()) {
                    $event->setThrowable(new NotFoundHttpException(\sprintf('The resource %s could not be found', $exception->getResource()), $exception));

                    return;
                }
            }
        }

        if ([] !== $errors) {
            $additionalErrors = \sprintf(' (%s)', implode(' / ', $errors));
        }

        $event->setThrowable(new BadRequestHttpException(\sprintf('%s%s', $exception->getMessage(), $additionalErrors ?? ''), $exception));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onSoapException', 999],
        ];
    }
}
