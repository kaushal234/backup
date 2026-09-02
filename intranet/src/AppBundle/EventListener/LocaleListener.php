<?php

declare(strict_types=1);

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleListener implements EventSubscriberInterface
{
    final public const LOCALE_PARAMETER = '_locale';

    /** @var array */
    private $language;

    /** @var string */
    private $defaultLocale;

    public function __construct($language = [], $defaultLocale = 'en')
    {
        $this->language = $language;
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $locale = $request->query->get(self::LOCALE_PARAMETER);

        if (\array_key_exists($locale, $this->language)) {
            $request->getSession()->set('_locale', $locale);
            $request->setLocale($locale);
        } else {
            if (!$request->hasPreviousSession()) {
                return;
            }

            $request->setLocale($request->getSession()->get('_locale') ?? $this->defaultLocale);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 15]],
        ];
    }
}
