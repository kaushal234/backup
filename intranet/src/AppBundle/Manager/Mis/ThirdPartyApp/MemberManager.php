<?php

declare(strict_types=1);

namespace AppBundle\Manager\Mis\ThirdPartyApp;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;

class MemberManager
{
    public const GRANT_ACCESS = 'GRANT_ACCESS';
    public const REMOVE_ACCESS = 'REMOVE_ACCESS';

    public function __construct(
        private readonly Client $client,
    ) {
    }

    /**
     * Remove member from third party app without update task.
     */
    public function forceRemove(int $memberId): void
    {
        $member = $this->client->find('modules/third_party_app/members', $memberId);

        $this->client->remove('modules/third_party_app/members', $memberId);
    }

    /**
     * Add member int third party app without update task.
     * Only update if needed.
     */
    public function forceAdd(ApiData $thirdPartyApp, string $peopleResource, bool $isAdmin = false): void
    {
        $member = $this->client->findBy('modules/third_party_app/members', [
            'thirdPartyApp' => $thirdPartyApp->getIri(),
            'user' => $peopleResource,
        ]);

        if ($member->count()) {
            $data = $member->first()->toArray();
            $data['user'] = $peopleResource;
            $data['thirdPartyApp'] = $thirdPartyApp->getIri();
            $data['admin'] = $isAdmin;
            $this->client->save('modules/third_party_app/members', $data);

            return;
        }

        $data['user'] = $peopleResource;
        $data['thirdPartyApp'] = $thirdPartyApp->getIri();
        $data['admin'] = $isAdmin;
        $this->client->post('modules/third_party_app/members', [
            'json' => $data,
        ]);
    }

    /**
     * Create update task to remove a member.
     */
    public function remove(int $memberId): void
    {
        $member = $this->client->find('modules/third_party_app/members', $memberId);

        $this->client->remove('modules/third_party_app/members', $memberId);

        $this->client->post('modules/blacklist/move', [
            'json' => [
                'user' => $member['user']['@id'],
                'module' => $member['thirdPartyApp']['@id'],
            ],
        ]);

        $this->client->post('modules/third_party_app/update_tasks', [
            'json' => [
                'user' => $member['user']['@id'],
                'thirdPartyApp' => $member['thirdPartyApp']['@id'],
                'demandType' => self::GRANT_ACCESS,
                'originType' => 'BLACKLIST',
            ],
        ]);
    }

    /**
     * Accept update task of GRANT_ACCESS and remove user from member list.
     */
    public function acceptGrantAccess(ApiData $updateTask): bool
    {
        if (false !== $updateTask['done'] || false !== $updateTask['confirmed']) {
            return false;
        }

        $updateTask['done'] = true;
        $updateTask['confirmed'] = true;
        $updateTask['thirdPartyApp'] = $updateTask['thirdPartyApp']['@id'];
        $updateTask['user'] = $updateTask['user']['@id'];
        unset($updateTask['createdBy'], $updateTask['updatedBy']);
        $this->client->save('modules/third_party_app/update_tasks', $updateTask);

        $data['user'] = $updateTask['user'];
        $data['thirdPartyApp'] = $updateTask['thirdPartyApp'];
        $this->client->post('modules/third_party_app/members', [
            'json' => $data,
        ]);

        return true;
    }

    /**
     * Deny update task of GRANT_ACCESS.
     */
    public function denyGrantAccess(ApiData $updateTask): bool
    {
        if (false !== $updateTask['done'] || false !== $updateTask['confirmed']) {
            return false;
        }

        $updateTask['done'] = true;
        $updateTask['confirmed'] = false;
        $updateTask['thirdPartyApp'] = $updateTask['thirdPartyApp']['@id'];
        $updateTask['user'] = $updateTask['user']['@id'];
        unset($updateTask['createdBy'], $updateTask['updatedBy']);
        $this->client->save('modules/third_party_app/update_tasks', $updateTask);

        return true;
    }

    /**
     * Accept update task of REMOVE_ACCESS and remove user from member list.
     */
    public function acceptRemoveAccess(ApiData $updateTask): bool
    {
        if (false !== $updateTask['done'] || false !== $updateTask['confirmed']) {
            return false;
        }

        $updateTask['done'] = true;
        $updateTask['confirmed'] = true;
        $updateTask['thirdPartyApp'] = $updateTask['thirdPartyApp']['@id'];
        $updateTask['user'] = $updateTask['user']['@id'];
        unset($updateTask['createdBy'], $updateTask['updatedBy']);
        $this->client->save('modules/third_party_app/update_tasks', $updateTask);

        $member = $this->client->findOneBy('modules/third_party_app/members', [
            'user' => $updateTask['user'],
            'thirdPartyApp' => $updateTask['thirdPartyApp'],
        ]);
        $this->client->remove('modules/third_party_app/members', $member['id']);

        return true;
    }

    /**
     * Deny update task of REMOVE_ACCESS.
     */
    public function denyRemoveAccess(ApiData $updateTask): bool
    {
        if (false !== $updateTask['done'] || false !== $updateTask['confirmed']) {
            return false;
        }

        $updateTask['done'] = true;
        $updateTask['confirmed'] = false;
        $updateTask['thirdPartyApp'] = $updateTask['thirdPartyApp']['@id'];
        $updateTask['user'] = $updateTask['user']['@id'];
        unset($updateTask['createdBy'], $updateTask['updatedBy']);
        $this->client->save('modules/third_party_app/update_tasks', $updateTask);

        return true;
    }

    public function promote($memberId): void
    {
        $this->client->put(\sprintf('modules/third_party_app/members/%s', $memberId), [
            'json' => ['admin' => true],
        ]);
    }

    public function demote($memberId): void
    {
        $this->client->put(\sprintf('modules/third_party_app/members/%s', $memberId), [
            'json' => ['admin' => false],
        ]);
    }
}
