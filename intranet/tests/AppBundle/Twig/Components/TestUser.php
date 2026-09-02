<?php

declare(strict_types=1);

namespace Tests\AppBundle\Twig\Components;

use ApiBundle\Model\User;

/**
 * Pre-defined test users matching the fixtures in api/fixtures/users.yaml
 * and their ACLs in api/fixtures/acls.yaml.
 */
enum TestUser: string
{
    case SUPERUSER = 'superuser';
    case USER_BASIC = 'user_basic';

    public function build(): User
    {
        return match ($this) {
            self::SUPERUSER => new User(
                iriId: '/people/12',
                iriType: 'people',
                username: 'user-superuser@tld.fr',
                firstname: 'user',
                lastname: 'superuser',
                disabled: false,
                hidden: false,
                expirationDate: '2099-01-01',
                photo: [],
                roles: ['ROLE_USER', User::ROLE_PASSWORD_NOT_EXPIRED],
                acls: ['SUPERUSER', 'ACL_AUTH_INTRANET', 'GG_ADMIN'],
                businessUnit: null,
                token: 'fake-token',
            ),

            self::USER_BASIC => new User(
                iriId: '/people/11',
                iriType: 'people',
                username: 'user-basic@tld.fr',
                firstname: 'user',
                lastname: 'basic',
                disabled: false,
                hidden: false,
                expirationDate: '2099-01-01',
                photo: [],
                roles: ['ROLE_USER', User::ROLE_PASSWORD_NOT_EXPIRED],
                acls: ['ACL_AUTH_INTRANET'],
                businessUnit: null,
                token: 'fake-token',
            ),
        };
    }
}
