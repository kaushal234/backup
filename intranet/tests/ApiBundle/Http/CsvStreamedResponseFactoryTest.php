<?php

declare(strict_types=1);

namespace Tests\ApiBundle\Http;

use ApiBundle\Client;
use ApiBundle\Http\CsvStreamedResponseFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CsvStreamedResponseFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testMultiplePagesCallsAPIMultipleTimes()
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode(['hydra:member' => [], 'hydra:totalItems' => 5])),
            new MockResponse("bla bla\nblo blo\n"),
            new MockResponse("bli bli\nblu blu\n"),
        ]);

        $securityProphecy = $this->prophesize(Security::class);
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getCacheDir()->shouldBeCalledOnce()->willReturn('');
        $kernel->isDebug()->shouldBeCalledTimes(3)->willReturn(false);
        $client = new Client($securityProphecy->reveal(), $eventDispatcherProphecy->reveal(), $kernel->reveal(), $httpClient, [
            'base_uri' => 'https://haproxy:8080',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $parameters = ['itemsPerPage' => 3];

        $csvStreamedResponseFactory = new CsvStreamedResponseFactory($client);
        $response = $csvStreamedResponseFactory->create('dummies', $parameters, 'pouet.csv');
        self::assertSame('inline; filename=pouet.csv', $response->headers->get('Content-Disposition'));
        self::assertSame('text/csv; charset=utf-8', $response->headers->get('Content-type'));

        $response->send();

        $this->expectOutputString("bla bla\nblo blo\nbli bli\nblu blu\n");
    }

    public function testFirstBadResponseWillWillStopCallingTheApi()
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode(['hydra:member' => [], 'hydra:totalItems' => 5])),
            new MockResponse("bla bla\nblo blo\n", ['http_code' => 400]),
        ]);

        $securityProphecy = $this->prophesize(Security::class);
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getCacheDir()->shouldBeCalledOnce()->willReturn('');
        $kernel->isDebug()->shouldBeCalledTimes(2)->willReturn(false);
        $client = new Client($securityProphecy->reveal(), $eventDispatcherProphecy->reveal(), $kernel->reveal(), $httpClient, [
            'base_uri' => 'https://haproxy:8080',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $parameters = ['itemsPerPage' => 3];

        $csvStreamedResponseFactory = new CsvStreamedResponseFactory($client);
        $response = $csvStreamedResponseFactory->create('dummies', $parameters, 'pouet.csv');
        self::assertSame('inline; filename=pouet.csv', $response->headers->get('Content-Disposition'));
        self::assertSame('text/csv; charset=utf-8', $response->headers->get('Content-type'));

        $response->send();

        $this->expectOutputString('');
    }
}
