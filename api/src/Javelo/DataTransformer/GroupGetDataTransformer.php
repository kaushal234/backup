<?php

declare(strict_types=1);

namespace App\Javelo\DataTransformer;

use App\Javelo\Repository\GroupRepository;
use Symfony\Component\Form\DataTransformerInterface;

class GroupGetDataTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): array
    {
        if (null === $value || !\array_key_exists('id', $value) || !\array_key_exists('members', $value)) {
            throw new \Exception("Group data is missing 'id' or 'members'.");
        }

        return [
            'id' => $value['id'],
            'members' => array_reduce($value['members'] ?? [], static function ($carry, $member) {
                $carry[$member['value']] = true;

                return $carry;
            }, []),
            GroupRepository::ADD_MEMBERS_KEY => [],
            GroupRepository::REMOVE_MEMBERS_KEY => [],
        ];
    }

    public function reverseTransform(mixed $value): mixed
    {
        throw new \Exception('Not implemented');
    }
}
