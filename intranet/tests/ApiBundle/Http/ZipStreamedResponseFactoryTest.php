<?php

declare(strict_types=1);

namespace Tests\ApiBundle\Http;

use ApiBundle\Client;
use ApiBundle\Http\ZipStreamedResponseFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ZipStreamedResponseFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testFilenameAndHeaders()
    {
        $httpClient = new MockHttpClient([
            new MockResponse('', [
                'headers' => [
                    'Content-Type' => 'application/zip',
                    'Accept' => 'application/zip',
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

        $fileStreamedResponseFactory = new ZipStreamedResponseFactory($client);

        $response = $fileStreamedResponseFactory->create('/x_files/y', 'test');

        self::assertSame('application/zip', $response->headers->get('Content-type'));
        self::assertSame('inline; filename=test.zip', $response->headers->get('Content-Disposition'));

        $response->send();
    }
}
