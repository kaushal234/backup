<?php

declare(strict_types=1);

namespace ApiBundle\Security\Core\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

class JwtToken extends PreAuthenticatedToken
{
    public function eraseCredentials(): void
    {
    }
}
