<?php

declare(strict_types=1);

namespace App\ION\Token;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class IONTokenProvider implements ServiceSubscriberInterface
{
    private readonly ContainerInterface $locator;
    private readonly CacheInterface $cache;
    private readonly string $ionUsername;
    private readonly string $ionPassword;

    public function __construct(ContainerInterface $locator, CacheInterface $cache, string $ionUsername, string $ionPassword)
    {
        $this->locator = $locator;
        $this->cache = $cache;
        $this->ionUsername = $ionUsername;
        $this->ionPassword = $ionPassword;
    }

    /**
     * @throws IdentityProviderException
     */
    public function getToken(): string
    {
        $token = $this->getAccessTokenAndCacheIt();

        if ($token->hasExpired()) {
            try {
                $token = $this->cache->get('refresh_ion_token', function (ItemInterface $item) use ($token) {
                    $item->expiresAfter(2 * 3599);

                    return $this->locator->get(GenericProvider::class)->getAccessToken('refresh_token', [
                        'refresh_token' => $token->getRefreshToken(),
                    ]);
                });
            } catch (IdentityProviderException $exception) {
                if ('invalid_grant' !== $exception->getMessage()) {
                    throw $exception;
                }
                $this->cache->delete('ion_token');
                $this->cache->delete('refresh_ion_token');

                $token = $this->getAccessTokenAndCacheIt();
            }
        }

        return $token->getToken();
    }

    public static function getSubscribedServices(): array
    {
        return [GenericProvider::class];
    }

    private function getAccessTokenAndCacheIt(): AccessTokenInterface
    {
        return $this->cache->get('ion_token', function () {
            return $this->locator->get(GenericProvider::class)->getAccessToken('password', [
                'username' => $this->ionUsername,
                'password' => $this->ionPassword,
            ]);
        });
    }
}
