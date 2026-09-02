<?php

declare(strict_types=1);

namespace Tests\ApiBundle\Http;

use ApiBundle\Client;
use ApiBundle\Http\FileStreamedResponseFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FileStreamedResponseFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testFileResponseContainsTheRightBodyAndHeaders()
    {
        $httpClient = new MockHttpClient([
            new MockResponse("francis\nla line\n", [
                'response_headers' => [
                    'Content-Disposition' => 'inline;',
                    'Content-type' => 'the third',
                ],
            ]),
        ]);

        $securityProphecy = $this->prophesize(Security::class);
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getCacheDir()->shouldBeCalledOnce()->willReturn('');
        $kernel->isDebug()->shouldBeCalledOnce()->willReturn(false);
        $client = new Client($securityProphecy->reveal(), $eventDispatcherProphecy->reveal(), $kernel->reveal(), $httpClient, [
            'base_uri' => 'https://haproxy:8080',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $fileStreamedResponseFactory = new FileStreamedResponseFactory($client);

        $response = $fileStreamedResponseFactory->create('/x_files/y');

        self::assertSame('inline;', $response->headers->get('Content-Disposition'));
        self::assertSame('the third', $response->headers->get('Content-type'));

        $response->send();

        $this->expectOutputString("francis\nla line\n");
    }

    public function testFileResponseForwardOptions()
    {
        $httpClient = new MockHttpClient([
            new MockResponse("francis\nla line\n"),
        ]);

        $securityProphecy = $this->prophesize(Security::class);
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getCacheDir()->shouldBeCalledOnce()->willReturn('');
        $kernel->isDebug()->shouldBeCalledOnce()->willReturn(false);
        $client = new Client($securityProphecy->reveal(), $eventDispatcherProphecy->reveal(), $kernel->reveal(), $httpClient, [
            'base_uri' => 'https://haproxy:8080',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $options = ['headers' => ['Content-type' => 'application/pdf']];

        $fileStreamedResponseFactory = new FileStreamedResponseFactory($client);

        $response = $fileStreamedResponseFactory->create('/x_files/y', $options, 'data');
        $response->send();

        $this->expectOutputString("francis\nla line\n");
    }

    public function testClientExceptionAreNotCaught()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('HTTP 400 returned for "https://x_files/y');

        $httpClient = new MockHttpClient([
            new MockResponse('Exceptional Exception', [
                'http_code' => 400,
            ]),
        ]);

        $securityProphecy = $this->prophesize(Security::class);
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getCacheDir()->shouldBeCalledOnce()->willReturn('');
        $kernel->isDebug()->shouldBeCalledOnce()->willReturn(false);
        $client = new Client($securityProphecy->reveal(), $eventDispatcherProphecy->reveal(), $kernel->reveal(), $httpClient, [
            'base_uri' => 'https://haproxy:8080',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $fileStreamedResponseFactory = new FileStreamedResponseFactory($client);
        $fileStreamedResponseFactory->create('/x_files/y');
    }

    public function testFileResponseContainsWhenRequestAttachment()
    {
        $httpClient = new MockHttpClient([
            new MockResponse("francis\nla line\n", [
                'response_headers' => [
                    'Content-Disposition' => 'inline;',
                    'Content-type' => 'the third',
                ],
            ]),
        ]);

        $securityProphecy = $this->prophesize(Security::class);
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getCacheDir()->shouldBeCalledOnce()->willReturn('');
        $kernel->isDebug()->shouldBeCalledOnce()->willReturn(false);
        $client = new Client($securityProphecy->reveal(), $eventDispatcherProphecy->reveal(), $kernel->reveal(), $httpClient, [
            'base_uri' => 'https://haproxy:8080',
        ]);

        $fileStreamedResponseFactory = new FileStreamedResponseFactory($client);

        $response = $fileStreamedResponseFactory->create('/x_files/y', [], null, 'new content disposition');

        self::assertSame('new content disposition;', $response->headers->get('Content-Disposition'));
        self::assertSame('the third', $response->headers->get('Content-type'));

        $response->send();

        $this->expectOutputString("francis\nla line\n");
    }
}
