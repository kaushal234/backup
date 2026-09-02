<?php

declare(strict_types=1);

namespace App\Auth;

use App\Client\ApiClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiAuthenticator
{
    private const COOKIE_NAME = '_jwt';
    private const PORTAL = 'intranet';

    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function authenticate(string $username, string $password): void
    {
        $response = $this->client->request('POST', '/token', [
            'body' => [
                'username' => $username,
                'password' => $password,
                'portal' => self::PORTAL,
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        $token = json_decode($response->getContent())->token;

        if ($this->client instanceof ApiClient) {
            $this->client->setToken($token);
        }

        setcookie(
            self::COOKIE_NAME,
            $token,
            time() + 86400,
            '/',
            '',
            false,
            true
        );
    }

    public function getUserFromAPI(int $userId): array
    {
        $response = $this->client->request('GET', \sprintf('/people/%s', $userId));

        return json_decode($response->getContent(), true);
    }
}
