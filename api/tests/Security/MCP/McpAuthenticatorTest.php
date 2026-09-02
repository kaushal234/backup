<?php

declare(strict_types=1);

namespace App\Tests\Security\MCP;

use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use App\Security\McpAuthenticator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use TheNetworg\OAuth2\Client\Provider\Azure;

class McpAuthenticatorTest extends TestCase
{
    use ProphecyTrait;

    private const TENANT_ID = 'aaaaaaaa-0000-0000-0000-000000000001';
    // Callers and audiences are drawn from the same allowlist (a caller app id can also be a valid audience)
    private const APP_ID_1 = 'bbbbbbbb-0000-0000-0000-000000000001';
    private const APP_ID_2 = 'bbbbbbbb-0000-0000-0000-000000000002';
    private const USER_EMAIL = 'user@example.com';
    private const KEY_ID = 'test-key-id';

    private \OpenSSLAsymmetricKey $privateKey;
    private Azure $azureProvider;

    protected function setUp(): void
    {
        $privateKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => \OPENSSL_KEYTYPE_RSA]);
        \assert($privateKey instanceof \OpenSSLAsymmetricKey);
        $this->privateKey = $privateKey;

        $details = openssl_pkey_get_details($privateKey);
        \assert(false !== $details);
        $publicKey = openssl_pkey_get_public($details['key']);
        \assert($publicKey instanceof \OpenSSLAsymmetricKey);

        $azureProvider = $this->prophesize(Azure::class);
        $azureProvider->getJwtVerificationKeys()->willReturn([self::KEY_ID => new Key($publicKey, 'RS256')]);
        $this->azureProvider = $azureProvider->reveal();
    }

    // supports()

    public function testSupportsReturnsTrueForMcpRootPath(): void
    {
        $this->assertTrue($this->makeAuthenticator()->supports(Request::create('/_mcp')));
    }

    public function testSupportsReturnsTrueForMcpSubPath(): void
    {
        $this->assertTrue($this->makeAuthenticator()->supports(Request::create('/_mcp/tools/list')));
    }

    public function testSupportsReturnsFalseForNonMcpPath(): void
    {
        $this->assertFalse($this->makeAuthenticator()->supports(Request::create('/api/people')));
    }

    // authenticate() — failure cases

    public function testAuthenticateThrowsWhenNoBearerToken(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Missing Bearer token.');

        $this->makeAuthenticator()->authenticate(Request::create('/_mcp'));
    }

    public function testAuthenticateThrowsWhenTokenIsInvalid(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid Azure token:');

        $this->makeAuthenticator()->authenticate($this->makeRequest('not.a.valid.jwt'));
    }

    public function testAuthenticateThrowsWhenTokenIsExpired(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Token expired');

        $token = $this->makeToken(['iat' => time() - 7200, 'nbf' => time() - 7200, 'exp' => time() - 3600]);
        $this->makeAuthenticator()->authenticate($this->makeRequest($token));
    }

    public function testAuthenticateThrowsWhenTenantIdDoesNotMatch(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid tenant.');

        $token = $this->makeToken(['tid' => 'wrong-tenant-id']);
        $this->makeAuthenticator()->authenticate($this->makeRequest($token));
    }

    public function testAuthenticateThrowsWhenCallerAppIdIsMissing(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Cannot determine calling client application (azp/appid missing).');

        $token = $this->makeToken(['appid' => null]);
        $this->makeAuthenticator()->authenticate($this->makeRequest($token));
    }

    public function testAuthenticateThrowsWhenCallerAppIsNotAllowed(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Calling client application is not allowed.');

        $unknownAppId = 'unknown-app-00000000-0000-0000-0000';
        $token = $this->makeToken(['appid' => $unknownAppId, 'aud' => 'api://'.$unknownAppId]);
        $this->makeAuthenticator()->authenticate($this->makeRequest($token));
    }

    public function testAuthenticateThrowsWhenAudienceDoesNotMatchThisApi(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Token audience does not match this API.');

        $token = $this->makeToken(['aud' => 'api://some-other-resource']);
        $this->makeAuthenticator()->authenticate($this->makeRequest($token));
    }

    public function testAuthenticateThrowsWhenScopeIsMissing(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Missing required scope.');

        $token = $this->makeToken(['scp' => '']);
        $this->makeAuthenticator()->authenticate($this->makeRequest($token));
    }

    public function testAuthenticateThrowsWhenScopeIsOnlyAPartialMatch(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Missing required scope.');

        $token = $this->makeToken(['scp' => 'prefixed_access_as_user']);
        $this->makeAuthenticator()->authenticate($this->makeRequest($token));
    }

    public function testAuthenticateThrowsWhenUserIsNotFound(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('User not found or disabled.');

        $repository = $this->prophesize(PeopleRepository::class);
        $repository->findOneBy(['email' => self::USER_EMAIL, 'disabled' => false, 'hidden' => false])->willReturn(null);

        $token = $this->makeToken();
        $passport = $this->makeAuthenticator($repository->reveal())->authenticate($this->makeRequest($token));
        $passport->getUser();
    }

    // authenticate() — success cases

    public function testAuthenticateSucceedsWithAppId(): void
    {
        $people = new People();
        $repository = $this->prophesize(PeopleRepository::class);
        $repository->findOneBy(['email' => self::USER_EMAIL, 'disabled' => false, 'hidden' => false])->willReturn($people);

        $passport = $this->makeAuthenticator($repository->reveal())->authenticate($this->makeRequest($this->makeToken()));

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
        $this->assertSame($people, $passport->getUser());
    }

    public function testAuthenticateSucceedsWithAzpOverAppId(): void
    {
        $people = new People();
        $repository = $this->prophesize(PeopleRepository::class);
        $repository->findOneBy(['email' => self::USER_EMAIL, 'disabled' => false, 'hidden' => false])->willReturn($people);

        // azp takes precedence over appid; appid set to second app to confirm azp is the one checked
        $token = $this->makeToken(['azp' => self::APP_ID_1, 'appid' => self::APP_ID_2]);
        $passport = $this->makeAuthenticator($repository->reveal())->authenticate($this->makeRequest($token));

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
        $this->assertSame($people, $passport->getUser());
    }

    public function testAuthenticateSucceedsForSecondAllowedApp(): void
    {
        $people = new People();
        $repository = $this->prophesize(PeopleRepository::class);
        $repository->findOneBy(['email' => self::USER_EMAIL, 'disabled' => false, 'hidden' => false])->willReturn($people);

        $token = $this->makeToken(['appid' => self::APP_ID_2]);
        $passport = $this->makeAuthenticator($repository->reveal())->authenticate($this->makeRequest($token));

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
        $this->assertSame($people, $passport->getUser());
    }

    public function testAuthenticateSucceedsWhenAudienceIsFirstAllowedEntry(): void
    {
        $people = new People();
        $repository = $this->prophesize(PeopleRepository::class);
        $repository->findOneBy(['email' => self::USER_EMAIL, 'disabled' => false, 'hidden' => false])->willReturn($people);

        // appid is the second allowlist entry, aud is the first — proves the audience check is independent of the caller check
        $token = $this->makeToken(['appid' => self::APP_ID_2, 'aud' => 'api://'.self::APP_ID_1]);
        $passport = $this->makeAuthenticator($repository->reveal())->authenticate($this->makeRequest($token));

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
        $this->assertSame($people, $passport->getUser());
    }

    public function testAuthenticateUsesUniqueNameAsFallbackWhenUpnIsMissing(): void
    {
        $people = new People();
        $repository = $this->prophesize(PeopleRepository::class);
        $repository->findOneBy(['email' => self::USER_EMAIL, 'disabled' => false, 'hidden' => false])->willReturn($people);

        $token = $this->makeToken(['upn' => null, 'unique_name' => self::USER_EMAIL]);
        $passport = $this->makeAuthenticator($repository->reveal())->authenticate($this->makeRequest($token));

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
        $this->assertSame($people, $passport->getUser());
    }

    // lifecycle

    public function testOnAuthenticationSuccessReturnsNull(): void
    {
        $tokenInterface = $this->prophesize(TokenInterface::class);
        $result = $this->makeAuthenticator()->onAuthenticationSuccess(Request::create('/_mcp'), $tokenInterface->reveal(), 'main');

        $this->assertNull($result);
    }

    public function testOnAuthenticationFailureReturnsUnauthorizedJsonResponse(): void
    {
        $exception = new AuthenticationException('Missing Bearer token.');
        $response = $this->makeAuthenticator()->onAuthenticationFailure(Request::create('/_mcp'), $exception);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testOnAuthenticationFailureLogsTheFailureReason(): void
    {
        $exception = new AuthenticationException('Missing Bearer token.');
        $request = Request::create('/_mcp');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with('MCP authentication failure', $this->callback(
                static fn (array $context) => 'Missing Bearer token.' === $context['reason']
            ));

        $this->makeAuthenticator(null, $logger)->onAuthenticationFailure($request, $exception);
    }

    // helpers

    private function makeToken(array $overrides = []): string
    {
        $payload = array_merge([
            'tid' => self::TENANT_ID,
            'appid' => self::APP_ID_1,
            'aud' => 'api://'.self::APP_ID_2,
            'scp' => 'access_as_user',
            'upn' => self::USER_EMAIL,
            'iat' => time(),
            'nbf' => time() - 10,
            'exp' => time() + 3600,
        ], $overrides);

        // Remove keys explicitly set to null to simulate missing JWT claims
        $payload = array_filter($payload, static fn ($value) => null !== $value);

        return JWT::encode($payload, $this->privateKey, 'RS256', self::KEY_ID);
    }

    private function makeRequest(string $bearerToken): Request
    {
        return Request::create('/_mcp', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$bearerToken]);
    }

    private function makeAuthenticator(?PeopleRepository $repository = null, ?LoggerInterface $logger = null): McpAuthenticator
    {
        $allowedAppIds = [self::APP_ID_1, self::APP_ID_2];

        return new McpAuthenticator(
            $this->azureProvider,
            $repository ?? $this->createMock(PeopleRepository::class),
            self::TENANT_ID,
            $allowedAppIds,
            $allowedAppIds,
            $logger ?? $this->createMock(LoggerInterface::class),
        );
    }
}
