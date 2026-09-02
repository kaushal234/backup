<?php

declare(strict_types=1);

namespace AppBundle\Security\RoleProvider;

use ApiBundle\Client;
use ApiBundle\Model\User;

readonly class FeatureProvider implements RoleProviderInterface
{
    public function __construct(
        private Client $client,
    ) {
    }

    public function loadRolesByUser(User $user): array
    {
        $featuresList = $this->client->findBy(
            'features',
            [
                'groups.acls.user' => \sprintf('users/%s', $user->getId()),
                'normalization_groups_override' => ['feature_list'],
            ],
            [],
            ['cache' => true]
        );

        return array_map(static function ($feature) {
            return $feature['name'];
        }, $featuresList->all());
    }
}
