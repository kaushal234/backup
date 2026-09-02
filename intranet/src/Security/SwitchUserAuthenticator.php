<?php

declare(strict_types=1);

namespace App\Security;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\User;
use ApiBundle\Security\Core\Authentication\UserProvider;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class SwitchUserAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    final public const SWITCH_PARAMETER = '_switch_to';
    final public const EXIT_PARAMETER = '_switch_exit';

    final public const LOGIN_ROUTE = 'login';

    private bool $isSwitching = false;
    private bool $isExitingSwitch = false;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserProvider $userProvider,
        private readonly RouterInterface $router,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly Client $client,
    ) {
    }

    public function supports(Request $request): bool
    {
        $this->isSwitching = $request->query->has(self::SWITCH_PARAMETER);
        $this->isExitingSwitch = $request->query->has(self::EXIT_PARAMETER);

        if ($this->isSwitching && $this->isExitingSwitch) {
            return false;
        }

        return $this->isExitingSwitch || ($this->isSwitching && '/people' === Iri::type($request->query->get(self::SWITCH_PARAMETER)));
    }

    public function authenticate(Request $request): Passport
    {
        if ($this->isSwitching) {
            /** @var User|null $user */
            $user = $this->tokenStorage->getToken()?->getUser();
            if (null === $user || [] === array_filter($user->getAcls(), static fn (string $role) => 'SUPERUSER' === $role)) {
                throw new AccessDeniedException();
            }
            $target = $request->query->get(self::SWITCH_PARAMETER);
            $username = $request->query->get('_username');
            $token = $this->authenticateSwitchUser((int) Iri::id($target));

            $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

            return new SelfValidatingPassport(new UserBadge(
                $username,
                function () use ($token) {
                    return $this->userProvider->loadUserByIdentifier($token);
                },
            ));
        }

        if ($this->isExitingSwitch) {
            /** @var SwitchUserToken $currentToken */
            $currentToken = $this->tokenStorage->getToken();

            $newToken = $this->logoutSwitchUser();

            return new SelfValidatingPassport(new UserBadge(
                $currentToken->getOriginalToken()->getUser()->getUserIdentifier(),
                function () use ($newToken) {
                    return $this->userProvider->loadUserByIdentifier($newToken);
                },
            ));
        }

        throw new AccessDeniedException();
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $user = $passport->getUser();

        return new SwitchUserToken(
            $user,
            $firewallName,
            $user->getRoles(),
            $this->tokenStorage->getToken()
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($this->isSwitching) {
            return new RedirectResponse($this->router->generate('directory_people_show', ['id' => Iri::id($request->query->get(self::SWITCH_PARAMETER))]));
        }

        return new RedirectResponse($this->router->generate('home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    private function logoutSwitchUser(): string
    {
        try {
            $response = json_decode((string) $this->client->request('user-tokens', null, null, 'DELETE')->getContent(), true);
        } catch (ClientException $e) {
            throw new \Exception($e->getMessage());
        }

        return $response['token'];
    }

    private function authenticateSwitchUser(int $id): string
    {
        try {
            $response = $this->client->post(\sprintf('user-tokens/%s', $id));
        } catch (ClientException $e) {
            throw new AuthenticationException('Invalid credentials.', 0, $e);
        }

        return $response['token'];
    }
}
