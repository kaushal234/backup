<?php

declare(strict_types=1);

namespace App\Tests\Security\MCP;

use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class McpAuthenticatorWebTest extends WebTestCase
{
    private const TENANT_ID = 'aaaaaaaa-0000-0000-0000-000000000001';
    private const APP_ID = 'bbbbbbbb-0000-0000-0000-000000000001';
    private const AUDIENCE_APP_ID = 'cccccccc-0000-0000-0000-000000000001';
    private const USER_EMAIL = 'mcp-test@example.com';

    public function testNoTokenReturns401(): void
    {
        $client = static::createClient();
        $client->request('GET', '/_mcp');

        $this->assertSame(401, $client->getResponse()->getStatusCode());
        $this->assertArrayHasKey('error', json_decode($client->getResponse()->getContent(), true));
    }

    public function testInvalidBearerTokenReturns401(): void
    {
        $client = static::createClient();
        $client->request('GET', '/_mcp', [], [], ['HTTP_AUTHORIZATION' => 'Bearer not.a.valid.jwt']);

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }

    public function testValidTokenWithUnknownUserReturns401(): void
    {
        $client = static::createClient();

        $repository = $this->createMock(PeopleRepository::class);
        $repository->method('findOneBy')->willReturn(null);
        static::getContainer()->set(PeopleRepository::class, $repository);

        $client->request('GET', '/_mcp', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$this->makeToken()]);

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }

    public function testValidTokenWithKnownUserPassesFirewall(): void
    {
        $client = static::createClient();

        $repository = $this->createMock(PeopleRepository::class);
        $repository->method('findOneBy')->willReturn(new People());
        static::getContainer()->set(PeopleRepository::class, $repository);

        $client->request('GET', '/_mcp', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$this->makeToken()]);

        $this->assertNotSame(401, $client->getResponse()->getStatusCode());
    }

    private function makeToken(): string
    {
        $payload = [
            'tid' => self::TENANT_ID,
            'appid' => self::APP_ID,
            'aud' => 'api://'.self::AUDIENCE_APP_ID,
            'scp' => 'access_as_user',
            'upn' => self::USER_EMAIL,
            'iat' => time(),
            'nbf' => time() - 10,
            'exp' => time() + 3600,
        ];

        return JWT::encode($payload, McpAzureProviderStub::getTestPrivateKey(), 'RS256', McpAzureProviderStub::KEY_ID);
    }
}
