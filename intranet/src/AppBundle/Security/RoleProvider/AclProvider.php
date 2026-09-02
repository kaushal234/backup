<?php

declare(strict_types=1);

namespace AppBundle\Security\RoleProvider;

use ApiBundle\Iri\Iri;
use ApiBundle\Model\User;
use AppBundle\Service\DataProvider;
use Symfony\Contracts\Cache\CacheInterface;

class AclProvider implements RoleProviderInterface
{
    private readonly DataProvider $dataProvider;

    private readonly CacheInterface $cache;

    public function __construct(DataProvider $dataProvider, CacheInterface $arrayCache)
    {
        $this->dataProvider = $dataProvider;
        $this->cache = $arrayCache;
    }

    public function loadRolesByUser(User $user)
    {
        $id = (string) $user->getId();

        return $this->cache->get("acl_provider_$id", function () use ($id, $user) {
            if (!empty($user->getAcls())) {
                return array_map(static fn (string $group): string => 'ACL_'.$group, $user->getAcls());
            }

            $roles = [];
            foreach ($this->dataProvider->findAll('acls', ['user' => "users/$id", 'normalization_groups_override' => ['acl_list']]) as $row) {
                $acl = 'ACL_'.$row['group']['name'];
                $roles[] = $acl;

                if ($row['location'] ?? false) {
                    $roles[] = $acl.'_'.Iri::id($row['location']);
                }
            }

            return $roles;
        });
    }
}
