<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\Directory\PeopleRepository;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use TheNetworg\OAuth2\Client\Provider\Azure;

class McpAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly Azure $mcpAzureProvider,
        private readonly PeopleRepository $peopleRepository,
        private readonly string $allowedTenantId,        // single tenant id
        private readonly array $allowedCallerAppIds,     // list of allowed calling apps (azp/appid)
        private readonly array $allowedAudiences,          // Azure app registration ids exposing the MCP API (expected token audiences)
        private readonly LoggerInterface $mcpLogger,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->getPathInfo(), '/_mcp');
    }

    public function authenticate(Request $request): Passport
    {
        $authHeader = $request->headers->get('Authorization', '');
        if (!str_starts_with($authHeader, 'Bearer ')) {
            throw new AuthenticationException('Missing Bearer token.');
        }

        $bearerToken = mb_substr($authHeader, 7);

        try {
            $keys = $this->mcpAzureProvider->getJwtVerificationKeys();
            JWT::$leeway = 60;
            $token = (array) JWT::decode($bearerToken, $keys);
        } catch (ExpiredException $e) {
            throw new AuthenticationException('Token expired', 0, $e);
        } catch (BeforeValidException $e) {
            throw new AuthenticationException('Token not yet valid (clock skew)', 0, $e);
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid Azure token: '.$e->getMessage(), 0, $e);
        }
        // Check tenant id
        $tid = $token['tid'] ?? null;
        if (!$tid || $tid !== $this->allowedTenantId) {
            throw new AuthenticationException('Invalid tenant.');
        }

        // Check caller: accept a LIST (Copilot agent app + Cowork plugin app)
        // Prefer azp if present (v2), else appid (v1)
        $clientAppId = $token['azp'] ?? ($token['appid'] ?? null);
        if (!$clientAppId) {
            throw new AuthenticationException('Cannot determine calling client application (azp/appid missing).');
        }

        if (!\in_array($clientAppId, $this->allowedCallerAppIds, true)) {
            throw new AuthenticationException('Calling client application is not allowed.');
        }

        // Verify the token audience is one of this API's app registrations (guards against cross-resource token replay)
        $aud = $token['aud'] ?? '';
        $expectedAudiences = array_merge(
            $this->allowedAudiences,
            array_map(static fn (string $id) => 'api://'.$id, $this->allowedAudiences),
        );
        if (!\in_array($aud, $expectedAudiences, true)) {
            throw new AuthenticationException('Token audience does not match this API.');
        }

        // Check scope, delegated scope must include access_as_user
        $scope = $token['scp'] ?? '';
        if (!\is_string($scope) || !preg_match('/(^| )access_as_user( |$)/', $scope)) {
            throw new AuthenticationException('Missing required scope.');
        }

        // extract email of user from token
        $email = $token['upn'] ?? $token['unique_name'] ?? null;
        if (!$email) {
            throw new AuthenticationException('Cannot extract email of user from Azure token.');
        }

        return new SelfValidatingPassport(
            new UserBadge($email, function (string $email) {
                $people = $this->peopleRepository->findOneBy([
                    'email' => $email,
                    'disabled' => false,
                    'hidden' => false,
                ]);

                if (!$people) {
                    throw new AuthenticationException('User not found or disabled.');
                }

                return $people;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->mcpLogger->warning('MCP authentication failure', [
            'reason' => $exception->getMessage(),
            'ip' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent'),
        ]);

        return new JsonResponse(
            ['error' => 'Authentication failed.'],
            Response::HTTP_UNAUTHORIZED,
        );
    }
}
