<?php

declare(strict_types=1);

namespace App\Security;

use ApiBundle\Client;
use ApiBundle\Security\Core\Authentication\JwtToken;
use ApiBundle\Security\Core\Authentication\UserProvider;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class FormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    final public const LOGIN_ROUTE = 'login';
    final public const HOME_ROUTE = 'home';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserProvider $userProvider,
        private readonly Client $client,
    ) {
    }

    public function supports(Request $request): bool
    {
        return (self::LOGIN_ROUTE === $request->attributes->get('_route') || $this->urlGenerator->generate('login') === $request->server->get('REQUEST_URI')) && $request->isMethod(Request::METHOD_POST);
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('_username', '');
        $password = $request->request->get('_password', '');

        $token = $this->authenticateUser($username, $password);

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        return new SelfValidatingPassport(new UserBadge(
            $username,
            function () use ($token) {
                return $this->userProvider->loadUserByIdentifier($token);
            },
        ), [
            new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
        ]);
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $user = $passport->getUser();

        return new JwtToken(
            $user,
            $firewallName,
            $user->getRoles()
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($request->hasSession()) {
            $targetPath = $this->getTargetPath($request->getSession(), $firewallName);

            // If the registered targetPath is the login page, we reset it
            $loginRoute = $this->getLoginUrl($request);
            if (null !== $targetPath && $loginRoute === mb_substr($targetPath, -mb_strlen($loginRoute))) {
                $targetPath = null;
            }
        }

        $targetPath ??= $this->urlGenerator->generate(self::HOME_ROUTE);

        return new RedirectResponse($targetPath);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    private function authenticateUser(string $username = '', string $password = ''): string
    {
        try {
            $response = $this->client->post('token', [
                'form_params' => [
                    'username' => $username,
                    'password' => $password,
                    'portal' => 'intranet',
                ],
                // Here we force the content-type to prevent HTTP client config to append "application/json" to it
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            ]);
        } catch (ClientException $e) {
            throw new AuthenticationException('Invalid credentials.', 0, $e);
        }

        return $response['token'];
    }
}
