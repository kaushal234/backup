<?php

declare(strict_types=1);

namespace AppBundle\EventListener;

use ApiBundle\Model\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class BusinessUnitCookieListener implements EventSubscriberInterface
{
    private const ALVEST_BUSINESS_UNIT_COOKIE_NAME = 'ALVEST_BUSINESS_UNIT';

    private readonly Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function setBusinessUnitCookie(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User || null === $user->getBusinessUnit()) {
            return;
        }

        $businessUnitIri = $user->getBusinessUnit()->getIriId();
        if ($businessUnitIri === $event->getRequest()->cookies->get(self::ALVEST_BUSINESS_UNIT_COOKIE_NAME)) {
            return;
        }

        $event->getResponse()->headers->setCookie(Cookie::create(
            self::ALVEST_BUSINESS_UNIT_COOKIE_NAME,
            $businessUnitIri,
            new \DateTimeImmutable('+1 Year'),
            '/',
            null,
            false,
            true,
            true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'setBusinessUnitCookie',
        ];
    }
}
