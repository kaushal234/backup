<?php

declare(strict_types=1);

namespace AppBundle\EventListener;

use ApiBundle\Client;
use ApiBundle\Model\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Stores the locale of the user in the session after the
 * login. This can be used by the LocaleListener afterwards.
 */
class UserLocaleListener implements EventSubscriberInterface
{
    private readonly RequestStack $requestStack;

    private readonly Client $client;

    public function __construct(RequestStack $requestStack, Client $client)
    {
        $this->requestStack = $requestStack;
        $this->client = $client;
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $session = $this->requestStack->getSession();
        if (!$session->has('_locale')) {
            $user = $event->getAuthenticationToken()->getUser();

            if (!$user instanceof User) {
                return;
            }
            $people = $this->client->find('people', $user->getId());
            if (isset($people['locale'])) {
                $session->set('_locale', $people['locale']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
        ];
    }
}
