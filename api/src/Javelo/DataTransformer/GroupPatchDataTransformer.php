<?php

declare(strict_types=1);

namespace App\Javelo\DataTransformer;

use App\Javelo\Repository\GroupRepository;
use Symfony\Component\Form\DataTransformerInterface;

class GroupPatchDataTransformer implements DataTransformerInterface
{
    /**
     * @throws \Exception
     */
    public function transform($value): array
    {
        if (empty($value[GroupRepository::ADD_MEMBERS_KEY]) && empty($value[GroupRepository::REMOVE_MEMBERS_KEY])) {
            throw new \Exception('No members found to add or remove');
        }
        $operations = [];

        if (!empty($value[GroupRepository::REMOVE_MEMBERS_KEY])) {
            $removedIds = array_map(static fn ($id) => ['value' => $id], $value[GroupRepository::REMOVE_MEMBERS_KEY]);
            $operations[] = ['op' => 'remove', 'path' => 'members', 'value' => $removedIds];
        }

        if (!empty($value[GroupRepository::ADD_MEMBERS_KEY])) {
            $addedIds = array_map(static fn ($id) => ['value' => $id], $value[GroupRepository::ADD_MEMBERS_KEY]);
            $operations[] = ['op' => 'add', 'path' => 'members', 'value' => $addedIds];
        }

        return [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
            'Operations' => $operations,
        ];
    }

    public function reverseTransform($value): mixed
    {
        throw new \Exception('Not implemented');
    }
}
