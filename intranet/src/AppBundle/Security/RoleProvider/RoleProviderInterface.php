<?php

declare(strict_types=1);

namespace AppBundle\Security\RoleProvider;

use ApiBundle\Model\User;

interface RoleProviderInterface
{
    /**
     * Retrieves the list of roles for the given user.
     *
     * @return array
     */
    public function loadRolesByUser(User $user);
}
