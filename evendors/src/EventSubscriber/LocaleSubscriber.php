<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Locale;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;

final class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LocaleSwitcher $localeSwitcher,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $locale = $event->getRequest()->cookies->get('_locale');
        if (Locale::isAvailable($locale)) {
            $this->localeSwitcher->setLocale($locale);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
