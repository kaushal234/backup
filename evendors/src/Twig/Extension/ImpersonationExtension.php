<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Attribute\AsTwigFunction;

use function in_array;

readonly class ImpersonationExtension
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    /**
     * Tells whether the current request is running under an impersonated session.
     *
     * Reads the JWT stored in the "_jwt" cookie, decodes its payload segment
     * (the second base64url-encoded part), and checks for the ROLE_IMPERSONATED
     * role. The token signature is not verified here — this is a display-only
     * check (e.g. to show an impersonation banner), not an authentication step.
     *
     * Returns false when the cookie is missing, malformed, or lacks the role.
     */
    #[AsTwigFunction('is_impersonating')]
    public function isImpersonating(): bool
    {
        $token = $this->requestStack->getCurrentRequest()?->cookies->get('_jwt');

        if (!$token) {
            return false;
        }

        $parts = explode('.', $token);

        if (!isset($parts[1])) {
            return false;
        }

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/'), true), true);

        return in_array('ROLE_IMPERSONATED', $payload['roles'] ?? [], true);
    }
}
