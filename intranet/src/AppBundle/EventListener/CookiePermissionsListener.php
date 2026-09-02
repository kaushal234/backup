<?php

declare(strict_types=1);

namespace AppBundle\EventListener;

use AppBundle\Controller\CookieController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CookiePermissionsListener implements EventSubscriberInterface
{
    private readonly Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function checkCookiePermissions(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        foreach (CookieController::COOKIES as $cookie) {
            if ($event->getRequest()->cookies->has($cookie) && !$this->security->isGranted('FEATURE_COOKIE_'.$cookie)) {
                $event->getResponse()->headers->clearCookie($cookie);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'checkCookiePermissions',
        ];
    }
}
