<?php

declare(strict_types=1);

namespace App\Javelo\Repository;

use App\Http\JaveloClient;
use App\Javelo\DataTransformer\GroupGetDataTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupClientRepository
{
    public function __construct(
        private readonly JaveloClient $javeloClient,
        private readonly GroupGetDataTransformer $getDataTransformer,
    ) {
    }

    public function getAllGroups(): array
    {
        $groups = [];
        $startIndex = 1;
        $error = [];
        do {
            $response = $this->javeloClient->doGroupRequest(['startIndex' => $startIndex]);

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                $error = [
                    'responseCode' => $response->getStatusCode(),
                    'errorMessage' => $response->getContent(),
                ];
            } else {
                $data = json_decode($response->getContent(), true);
                foreach ($data['Resources'] as $groupData) {
                    if (str_starts_with($groupData['displayName'], 'BU_')) {
                        $groups[$groupData['displayName']] = $this->getDataTransformer->transform($groupData);
                    }
                }

                $startIndex += \count($data['Resources']);
            }
        } while (!empty($data['Resources']) && !$error);

        if (empty($groups) || !empty($error)) {
            throw new \Exception(\sprintf('Invalid response on getAllGroups : %s', empty($error) ? 'no groups on Javelo' : $error['errorMessage']));
        }

        return $groups;
    }

    public function updateGroup($group): void
    {
        if (empty($group[GroupRepository::ADD_MEMBERS_KEY]) && empty($group[GroupRepository::REMOVE_MEMBERS_KEY])) {
            return;
        }
        $this->javeloClient->doGroupRequest(['body' => $group], $group['id'], Request::METHOD_PATCH);
    }
}
