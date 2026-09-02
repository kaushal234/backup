<?php

declare(strict_types=1);

namespace App\Sdk\TokenProvider;

use App\Security\Security;
use App\Security\User\User;

final class UserTokenProvider implements TokenProviderInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    /**
     * @return non-empty-string
     *
     * @mutation-free
     */
    public function getToken(): string
    {
        /** @var User $user */
        $user = $this->security->getAuthenticatedUser();

        return $user->token;
    }
}
