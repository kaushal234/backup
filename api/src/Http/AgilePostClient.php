<?php

declare(strict_types=1);

namespace App\Http;

use App\Agile\Resources\User;
use App\Agile\Serializer\UserPostSerializer;
use App\Agile\Serializer\UserSuspendSerializer;
use App\Agile\UserEventResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AgilePostClient
{
    private const URL_POST_USERS = 'webhooks';

    public function __construct(
        private readonly HttpClientInterface $agilePostClient,
        private readonly UserSuspendSerializer $userSuspendSerializer,
        private readonly UserPostSerializer $userPostSerializer,
    ) {
    }

    public function doRequest(User $user, string $event): array
    {
        $body = [
            'id' => \sprintf('ALVEST_PEOPLE_%s_%s', mb_strtoupper($event), $user->peopleId),
            'timestamp' => date(\DateTime::ATOM),
            'eventType' => $event,
            'content' => [
                'user' => UserEventResolver::USER_SUSPENDED === $event
                    ? $this->userSuspendSerializer->serialize($user)
                    : $this->userPostSerializer->serialize($user),
            ],
        ];

        $response = $this->agilePostClient->request(Request::METHOD_POST, self::URL_POST_USERS, [
            'json' => $body,
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \Exception('Invalid response on '.$event.' user: '.$user->email.' errorCode:'.$response->getStatusCode().' errorMessage:'.$response->getContent(false));
        }

        return json_decode($response->getContent(), true);
    }
}
