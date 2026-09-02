<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Locale;
use App\Security\User\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
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

    public function onLoginSuccessSetLocaleCookies(LoginSuccessEvent $event): void
    {
        $response = $event->getResponse();
        if (null === $response) {
            return;
        }

        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $locale = Locale::fromApiLanguage($user->getLanguage());

        $response->headers->setCookie(Cookie::create('_locale', $locale->value));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            LoginSuccessEvent::class => 'onLoginSuccessSetLocaleCookies',
        ];
    }
}
