<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\EmailValidationException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class EmailValidationExceptionListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onEmailValidationException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof EmailValidationException) {
            return;
        }

        $logParts = [];

        foreach ($exception->getViolations() as $violation) {
            $logParts[] = \sprintf('%s: %s', $violation->getPropertyPath(), (string) $violation->getMessage());
        }

        $this->serviceLocator->get('monolog.logger.emails')->error(\sprintf('Validation error for email "%s" - %s', $exception->getSubject(), implode(', ', $logParts)));
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onEmailValidationException'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            'monolog.logger.emails' => LoggerInterface::class,
        ];
    }
}
