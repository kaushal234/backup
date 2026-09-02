<?php

declare(strict_types=1);

namespace AppBundle\Manager\Directory;

use ApiBundle\Client;
use ApiBundle\Model\User;

class TeamMemberManager
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function getFlatTechnicians(): array
    {
        /** @var User $user */
        $user = $this->client->get('/me');
        $queryParameters = ['position' => ['AST', 'CSTL']];

        if (null !== $user['position']) {
            if ('AST' === $user['position']['code']) {
                $queryParameters['supervisor_position'] = ['CSM', 'CSTL'];
            } elseif ('CSTL' === $user['position']['code'] || 'CSS' === $user['position']['code']) {
                $queryParameters['supervisor_position'] = ['CSM'];
            }
        }

        $queryParameters['order']['lastname'] = 'ASC';

        return $this->client->get('/people/team_members', [
            'query' => $queryParameters,
        ]);
    }

    /* Recursive branch extrusion */
    public function createBranch(&$parents, $children)
    {
        $tree = [];
        foreach ($children as $child) {
            if (isset($parents[$child['id']])) {
                $child['children'] = $this->createBranch($parents, $parents[$child['id']]);
            }

            $tree[] = $child;
        }

        return $tree;
    }

    /* Initialization */
    public function createTree($flat, $root = 0)
    {
        $parents = [];
        foreach ($flat as $a) {
            if (array_filter($flat, static function ($user) use ($a, $root) { return $a['supervisor_id'] === $user['id'] || $root === $user['id']; })) {
                $parents[$a['supervisor_id']][] = $a;
            } else {
                $parents[$root][] = $a;
            }
        }

        if (empty($parents)) {
            return null;
        }

        $mainUser = array_filter($flat, static function ($user) use ($root) { return $root === $user['id']; });

        if (0 === \count($mainUser)) {
            return $this->createBranch($parents, $parents[$root]);
        }

        $mainUser = $mainUser[0];
        $mainUser['children'] = $this->createBranch($parents, $parents[$root]);

        return $mainUser;
    }
}
