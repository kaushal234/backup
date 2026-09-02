<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\Http\Responder;
use App\Sdk\Http\ClientInterface;
use App\Security\Security;
use App\Security\User\UserProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

use function in_array;

final class Authenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'security:login';

    public function __construct(
        private readonly Responder $responder,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserProvider $userProvider,
        private readonly ClientInterface $client,
    ) {
    }

    public function supports(Request $request): bool
    {
        return parent::supports($request);
    }

    public function authenticate(Request $request): Passport
    {
        $identifier = $request->request->get('_identifier', '');
        $password = $request->request->get('_password', '');

        $token = $this->getToken($identifier, $password);

        $request->getSession()->set(Security::LAST_USERNAME, $identifier);

        return new SelfValidatingPassport(new UserBadge(
            $identifier,
            function () use ($token) {
                return $this->userProvider->loadUserByIdentifier($token);
            },
        ), [
            new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            new RememberMeBadge(),
        ]);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return $this->responder->route('index');
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    /**
     * @return non-empty-string
     */
    private function getToken(string $identifier, string $password): string
    {
        $tokenResponse = $this->client->request('POST', '/token', [
            'body' => [
                'username' => $identifier,
                'password' => $password,
                'portal' => 'evendors',
            ],
        ]);

        if ($tokenResponse->getStatusCode() > 299 && (!in_array($tokenResponse->getStatusCode(), [400, 422], true))) {
            throw new BadCredentialsException();
        }

        /** @var non-empty-string */
        return $tokenResponse->toArray()['token'];
    }
}
