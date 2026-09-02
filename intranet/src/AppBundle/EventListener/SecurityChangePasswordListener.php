<?php

declare(strict_types=1);

namespace AppBundle\EventListener;

use ApiBundle\Model\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SecurityChangePasswordListener implements EventSubscriberInterface
{
    private readonly RouterInterface $router;

    private readonly string $redirectRouteName;

    private readonly Security $security;

    public function __construct(RouterInterface $router, string $redirectRouteName, Security $security)
    {
        $this->router = $router;
        $this->redirectRouteName = $redirectRouteName;
        $this->security = $security;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $session = $event->getRequest()->getSession();

        if (!$exception instanceof AccessDeniedException || null === $this->security->getUser()) {
            return;
        }

        if (!$this->security->isGranted(User::ROLE_PASSWORD_NOT_EXPIRED)) {
            if ($session instanceof Session) {
                $session->getFlashBag()->add('warning', 'You need to update your password');
            }
            $event->setResponse(new RedirectResponse($this->router->generate($this->redirectRouteName)));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 2],
        ];
    }
}
