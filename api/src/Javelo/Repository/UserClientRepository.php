<?php

declare(strict_types=1);

namespace App\Javelo\Repository;

use App\Entity\Directory\People;
use App\Http\JaveloClient;
use App\Javelo\Event\LogUserClientEvent;
use App\Javelo\Resources\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class UserClientRepository
{
    private const DESERIALIZE_FORMAT = 'json';

    public function __construct(
        private readonly JaveloClient $javeloClient,
        private readonly SerializerInterface $serializer,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getUserById(string $id): ?User
    {
        $response = $this->javeloClient->doUserRequest([], $id);
        if (Response::HTTP_NOT_FOUND === $response->getStatusCode()) {
            return null;
        }
        if (Response::HTTP_OK === $response->getStatusCode()) {
            return $this->deserializeData($response->getContent());
        }

        throw new \Exception('Invalid response on getUserById id: '.$id);
    }

    public function findUserByEmail(string $email): ?User
    {
        $response = $this->javeloClient->doUserRequest(['filter' => 'userName eq '.$email]);
        if (Response::HTTP_OK === $response->getStatusCode()) {
            $data = json_decode($response->getContent(), true);
            if (!empty($data['Resources'])) {
                return $this->deserializeData(json_encode($data['Resources'][0]));
            }

            return null;
        }

        throw new \Exception('Invalid response on findUserMethod email: '.$email);
    }

    public function updateUser(User $javeloUser, array $changes, ?People $poster = null): void
    {
        $response = $this->javeloClient->doUserRequest(['body' => $javeloUser], $javeloUser->id, Request::METHOD_PATCH);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \Exception('Invalid response on update user: '.$javeloUser->userName.' errorCode:'.$response->getStatusCode().' errorMessage:'.$response->getContent());
        }
        $this->eventDispatcher->dispatch(new LogUserClientEvent($javeloUser, $changes, $poster));
    }

    public function createUser(User $javeloUser, array $changes, ?People $poster = null): void
    {
        $response = $this->javeloClient->doUserRequest(['body' => $javeloUser], null, method: Request::METHOD_POST);
        if (Response::HTTP_CREATED !== $response->getStatusCode()) {
            throw new \Exception('Invalid response on create user: '.$javeloUser->userName.' errorCode:'.$response->getStatusCode().' errorMessage:'.$response->getContent());
        }
        $this->eventDispatcher->dispatch(new LogUserClientEvent($javeloUser, $changes, $poster));
        $javeloUser->id = $response->toArray()['id'];
    }

    /**
     * @return User[]
     */
    public function getAllUsers(): array
    {
        $users = [];
        $startIndex = 1;
        $error = [];
        do {
            $response = $this->javeloClient->doUserRequest(['startIndex' => $startIndex]);
            if (Response::HTTP_OK !== $response->getStatusCode()) {
                $error = [
                    'responseCode' => $response->getStatusCode(),
                    'errorMessage' => $response->getContent(),
                ];
                break;
            }

            $data = json_decode($response->getContent(), true);
            foreach ($data['Resources'] as $userData) {
                $users[] = $this->deserializeData(json_encode($userData));
            }

            $startIndex += \count($data['Resources']);
        } while (!empty($data['Resources']));
        // We never want to recreate some or all users with a partial or empty list from Javelo
        if (empty($users) || !empty($error)) {
            throw new \Exception(\sprintf('Invalid response on getAllUsers : %s', empty($error) ? 'no users on Javelo' : $error['errorMessage']));
        }

        return $users;
    }

    private function deserializeData(string $data): User
    {
        return $this->serializer->deserialize($data, User::class, self::DESERIALIZE_FORMAT);
    }
}
