<?php

declare(strict_types=1);

namespace Tests\ApiBundle;

use ApiBundle\Client;
use ApiBundle\Model\BusinessUnit;
use ApiBundle\Model\User;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ClientTest extends TestCase
{
    use ProphecyTrait;

    public function testSimpleGetRequest()
    {
        $callback = function ($method, $url, $options) {
            $this->assertSame('GET', $method);
            $this->assertSame('https://example.com/testgeturl', $url);
            $this->assertArrayHasKey('option1', $options['extra']);
            $this->assertSame(42, $options['extra']['option1']);

            return new MockResponse('{"hydra:member":[], "hydra:totalItems":0}');
        };

        $apiClient = $this->getClientWithResponse($callback);

        $reponse = $apiClient->get('testgeturl', ['extra' => ['option1' => 42]]);
        $this->assertArrayHasKey('hydra:member', $reponse);
        $this->assertArrayHasKey('hydra:totalItems', $reponse);
        $this->assertCount(2, $reponse);
        $this->assertSame(0, $reponse['hydra:totalItems']);
    }

    public function testSimplePostRequest()
    {
        $callback = function ($method) {
            $this->assertSame('POST', $method);

            return new MockResponse('');
        };

        $apiClient = $this->getClientWithResponse($callback);

        $apiClient->post('testposturl');
    }

    public function testSimplePutRequest()
    {
        $callback = function ($method) {
            $this->assertSame('PUT', $method);

            return new MockResponse('');
        };

        $apiClient = $this->getClientWithResponse($callback);

        $apiClient->put('testputurl');
    }

    public function testExtraOptionsRequest()
    {
        $callback = function ($method, $url, $options) {
            $this->assertSame('GET', $method);
            $this->assertSame('params1=42&raw_results=1', $options['body']);
            $this->assertArrayNotHasKey('form_params', $options);
            $this->assertArrayNotHasKey('raw_results', $options);

            return new MockResponse('');
        };

        $apiClient = $this->getClientWithResponse($callback);

        $apiClient->doClientRequest('GET', 'testExtraOptions', [
            'form_params' => ['params1' => '42'],
            'raw_results' => true,
        ]);
    }

    public function testTokenIsSet()
    {
        $capturedOptions = [];
        $callback = static function (string $method, string $url, array $options) use (&$capturedOptions) {
            $capturedOptions = $options;

            return new MockResponse('{"hydra:member":[], "hydra:totalItems":0}');
        };

        $httpClient = new MockHttpClient($callback);
        $securityProphecy = $this->prophesize(Security::class);
        $user = new User(
            iriId: 'foo',
            iriType: 'User',
            username: 'foo@bar.fr',
            firstname: 'foo',
            lastname: 'bar',
            disabled: false,
            hidden: false,
            expirationDate: '',
            photo: [],
            roles: [],
            acls: [],
            businessUnit: new BusinessUnit('bar', 'BusinessUnit', 9, 'foorbar'),
            token: 'tokentest'
        );
        $securityProphecy->getUser()->willReturn($user);
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getCacheDir()->shouldBeCalledOnce()->willReturn('');
        $kernel->isDebug()->shouldBeCalledOnce()->willReturn(false);
        $apiClient = new Client($securityProphecy->reveal(), $eventDispatcherProphecy->reveal(), $kernel->reveal(), $httpClient, [
            'base_uri' => 'https://haproxy:8080',
        ]);

        $apiClient->request('testtoken', []);

        $this->assertContains('Authorization: Bearer tokentest', $capturedOptions['normalized_headers']['authorization'] ?? []);
    }

    protected function getClientWithResponse(callable $callback): Client
    {
        $httpClient = new MockHttpClient($callback);

        $securityProphecy = $this->prophesize(Security::class);
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getCacheDir()->shouldBeCalledOnce()->willReturn('');
        $kernel->isDebug()->shouldBeCalledOnce()->willReturn(false);

        return new Client($securityProphecy->reveal(), $eventDispatcherProphecy->reveal(), $kernel->reveal(), $httpClient, [
            'base_uri' => 'https://example.com',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }
}
