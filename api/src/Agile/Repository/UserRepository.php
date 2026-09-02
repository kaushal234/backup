<?php

declare(strict_types=1);

namespace App\Agile\Repository;

use App\Agile\Event\LogUserUpdateOnClientEvent;
use App\Agile\Resources\User;
use App\Entity\Directory\People;
use App\Http\AgileGetClient;
use App\Http\AgilePostClient;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UserRepository
{
    private const DESERIALIZE_FORMAT = 'json';

    public function __construct(
        private readonly AgileGetClient $agileGetClient,
        private readonly AgilePostClient $agilePostClient,
        private readonly SerializerInterface $serializer,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @return User[]
     */
    public function getAllUsers(): array
    {
        $users = [];
        $pageNumber = 1;
        do {
            $data = $this->agileGetClient->doRequest(['page' => $pageNumber, 'perPage' => 1000]);
            foreach ($data['results'] as $userData) {
                // exclude people don't come from People API
                if (isset($userData['ref'])) {
                    $users[$userData['id']] = $this->deserializeData($userData);
                }
            }
            ++$pageNumber;
        } while (!empty($data['results']));
        // We never want to recreate all User
        if (empty($users)) {
            throw new \Exception('Invalid response on getAllUsers : no users on Agile');
        }

        return $users;
    }

    public function updateAgileUser(User $user, array $changes, string $event): void
    {
        $this->agilePostClient->doRequest($user, $event);

        $this->eventDispatcher->dispatch(new LogUserUpdateOnClientEvent($user, $changes));
    }

    /**
     * @param User[] $agilePeople
     */
    public function searchAgileUserConcerned(People $people, array $agilePeople): ?User
    {
        foreach ($agilePeople as $agileUser) {
            if ($agileUser->peopleId === (string) $people->getId()) {
                // search and add managerIdPeopleId
                $agileUser->managerPeopleId = isset($agilePeople[$agileUser->managerAgileId])
                    ? (string) $agilePeople[$agileUser->managerAgileId]->peopleId
                    : '';

                return $agileUser;
            }
        }

        return null;
    }

    private function deserializeData(array $data): User
    {
        if (!isset($data['additionalFields'])) {
            $data['additionalFields'] = [];
        }

        return $this->serializer->deserialize(json_encode($data), User::class, self::DESERIALIZE_FORMAT);
    }
}
