<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\Security\User\UserProvider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class SwitchUserAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;
    final public const SWITCH_PARAMETER = '/impersonate';
    final public const JWT_COOKIE = '_jwt';
    public const LOGIN_ROUTE = 'security:login';
    public const INDEX_ROUTE = 'index';

    private bool $isSwitching = false;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserProvider $userProvider,
        private readonly RouterInterface $router,
        private readonly Security $security,
    ) {
    }

    public function supports(Request $request): bool
    {
        $this->isSwitching = self::SWITCH_PARAMETER === $request->getPathInfo() && $request->query->has('_userIdentifier') && $request->cookies->has(self::JWT_COOKIE);

        return $this->isSwitching;
    }

    public function authenticate(Request $request): Passport
    {
        if (null !== $this->security->getUser()) {
            $this->security->logout(false);
        }
        if ($this->isSwitching) {
            /** @var non-empty-string $jwt */
            $jwt = $request->cookies->get(self::JWT_COOKIE);
            $request->cookies->remove(self::JWT_COOKIE);
            $userIdentifier = $request->query->get('_userIdentifier');

            return new SelfValidatingPassport(new UserBadge(
                $userIdentifier,
                function () use ($jwt) {
                    return $this->userProvider->loadUserByIdentifier($jwt);
                },
            ));
        }

        throw new AccessDeniedException();
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        return new RedirectResponse($this->router->generate(self::INDEX_ROUTE));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
