<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Service\TechnicianOnCall\TechnicianOnCallSurvey;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallSurveyNotifier;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[AsEventListener(event: KernelEvents::VIEW, method: 'onWrite', priority: EventPriorities::POST_WRITE)]
readonly class TechnicianOnCallSurveyNotifierListener implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function onWrite(ViewEvent $event): void
    {
        $survey = $event->getControllerResult();

        if (!$survey instanceof TechnicianOnCallSurvey) {
            return;
        }

        if ('POST' !== $event->getRequest()->getMethod() && 'PUT' !== $event->getRequest()->getMethod()) {
            return;
        }

        /** @var TechnicianOnCallSurveyNotifier $notifier */
        $notifier = $this->container->get(TechnicianOnCallSurveyNotifier::class);
        $notifier->send($survey);
    }

    public static function getSubscribedServices(): array
    {
        return [
            TechnicianOnCallSurveyNotifier::class,
        ];
    }
}
