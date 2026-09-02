<?php

declare(strict_types=1);

namespace AppBundle\EventSubscriber;

use ApiBundle\Client;
use ApiBundle\Model\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class UserSettingsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private Client $client,
    ) {
    }

    /**
     * Load all user settings on the session when the user logs in.
     */
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();

        $settings = $this->client->findBy('user_settings', [
            'user' => $user->getId(),
        ]);

        foreach ($settings->getIterator() as $setting) {
            $this->requestStack->getSession()->set($setting['name'], $setting['settings']);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }
}
