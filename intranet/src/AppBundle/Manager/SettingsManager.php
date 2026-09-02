<?php

declare(strict_types=1);

namespace AppBundle\Manager;

use ApiBundle\Client;
use ApiBundle\Model\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class SettingsManager
{
    public function __construct(
        private readonly Client $client,
        private readonly Security $security,
        private readonly RequestStack $request,
    ) {
    }

    /**
     * Save the user setting in the database and add it to the session.
     */
    public function set(string $key, mixed $value): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $this->remove($key);

        // If no value to save, don't do the call
        if (!empty($value)) {
            $this->client->save('user_settings', [
                'user' => $user->getIriId(),
                'name' => $key,
                'settings' => $value,
            ]);
        }

        $this->request->getSession()->set($key, $value);
    }

    /**
     * Get user setting from the session.
     */
    public function get(string $key): mixed
    {
        return $this->request->getSession()->get($key);
    }

    /**
     * Remove user setting from the database and from the session.
     */
    public function remove($key): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $userSetting = $this->client->findBy('user_settings', [
            'user' => $user->getIriId(),
            'name' => $key,
        ]);
        $userSetting = $userSetting->first();
        if ($userSetting) {
            $this->client->remove('user_settings', $userSetting['id']);
        }

        $this->request->getSession()->remove($key);
    }
}
