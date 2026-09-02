<?php

declare(strict_types=1);

namespace AppBundle\EventListener;

use ApiBundle\Model\User;
use AppBundle\Security\RoleProvider\AclProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class EmailCookieListener implements EventSubscriberInterface
{
    private const COOKIE_NAME = 'COOKIE_CAT';

    private const ATTRIBUTE_NAME = 'haproxy-cookie-content';

    /** @var AclProvider */
    private $aclProvider;

    public function __construct(AclProvider $aclProvider)
    {
        $this->aclProvider = $aclProvider;
    }

    public function onLogin(LoginSuccessEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();

        $cookieContent = \sprintf('u=%s|', $user->getUserIdentifier());

        $acls = $this->aclProvider->loadRolesByUser($user);

        foreach ($acls as $acl) {
            $cookieContent .= \sprintf('g=%s|', mb_substr($acl, 4));
        }

        $event->getRequest()->getSession()->set(self::ATTRIBUTE_NAME, $cookieContent);
    }

    public function setUserEmailCookie(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $event->getResponse()->headers->setCookie(Cookie::create(
            self::COOKIE_NAME,
            $event->getRequest()->getSession()->get(self::ATTRIBUTE_NAME),
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
            KernelEvents::RESPONSE => 'setUserEmailCookie',
            LoginSuccessEvent::class => 'onLogin',
        ];
    }
}
