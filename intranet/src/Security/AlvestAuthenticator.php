<?php

declare(strict_types=1);

namespace App\Security;

use ApiBundle\Client;
use ApiBundle\Security\Core\Authentication\JwtToken;
use ApiBundle\Security\Core\Authentication\UserProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use TheNetworg\OAuth2\Client\Provider\Azure;

class AlvestAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    final public const LOGIN_ROUTE = 'login';
    final public const HOME_ROUTE = 'home';
    private ?string $state = null;
    private ?string $code = null;

    public function __construct(
        private readonly Azure $alvestAuthenticator,
        private readonly Client $client,
        private readonly LoggerInterface $logger,
        private readonly UserProvider $userProvider,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function supports(Request $request): bool
    {
        if ($request->query->has('code')) {
            $this->code = $request->query->get('code');
        }

        if ($request->query->has('state')) {
            $this->state = $request->query->get('state');
        }

        return '/alvest-login' === $request->getPathInfo();
    }

    public function authenticate(Request $request): Passport
    {
        if (null === $this->code) {
            throw new AuthenticationException();
        }

        if (null === $this->state || $request->getSession()->get('alvest-sso-state') !== $this->state) {
            throw new AccessDeniedException();
        }

        $response = $this->getToken($this->code);
        $token = $response['token'];
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $response['username']);

        return new SelfValidatingPassport(new UserBadge(
            $response['username'],
            function () use ($token) {
                return $this->userProvider->loadUserByIdentifier($token);
            },
        ));
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

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $authorizationUrl = \sprintf('%s&login_hint=%s', $this->alvestAuthenticator->getAuthorizationUrl(), $request->query->get('email'));
        $request->getSession()->set('alvest-sso-state', $this->alvestAuthenticator->getState());

        return new RedirectResponse($authorizationUrl);
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

    public function getToken(string $code): array
    {
        try {
            $accessToken = $this->alvestAuthenticator->getAccessToken(
                'authorization_code',
                [
                    'code' => $code,
                ]
            );

            $multiPart['token'] = $accessToken->getToken();
            $formData = new FormDataPart($multiPart);
        } catch (IdentityProviderException $exception) {
            $this->logger->error('Something went wrong getting access token from Alvest Azure: {error}', [
                'error' => $exception->getMessage(),
                'exception' => $exception,
            ]);
            throw new AuthenticationException();
        }

        try {
            $response = $this->client->request('token-alvest', null, null, Request::METHOD_POST, [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]);

            return $response->toArray();
        } catch (ClientException $exception) {
            $this->logger->error('Something went wrong getting a token from our API : {error}', [
                'error' => $exception->getMessage(),
                'exception' => $exception,
            ]);
            throw new AuthenticationException();
        }
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
