<?php

declare(strict_types=1);

namespace Tests\ApiBundle;

use ApiBundle\Client;
use ApiBundle\ClientExceptionMapper;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ClientExceptionMapperTest extends TestCase
{
    use ProphecyTrait;

    /** @dataProvider exceptionProvider */
    public function testMapToString($body, $expected)
    {
        $client = $this->getClientWithResponse($body);

        try {
            $reponse = $client->request('');
            $reponse->getContent();

            $this->fail();
        } catch (ClientException $e) {
            $mapper = new ClientExceptionMapper();
            $result = $mapper->mapToString($e);

            $this->assertSame($expected, $result);
        }
    }

    public function exceptionProvider()
    {
        $body = <<<JSON
{
    "@context": "\/contexts\/Error",
    "@type": "hydra:Error",
    "hydra:title": "An error occurred",
    "hydra:description": "Status IN PROGRESS is not allowed. Reasons:The following linked SOLs haven't reached the factory: 25470"
}
JSON;

        $expected = <<<'EOF'
An error occurred
Status IN PROGRESS is not allowed. Reasons:The following linked SOLs haven't reached the factory: 25470
EOF;

        yield 'Standard error without violations' => [$body, $expected];

        $body = <<<JSON
{
    "@context": "\/contexts\/Error",
    "@type": "hydra:Error"
}
JSON;
        yield 'Malformed error without violations' => [$body, 'An unexpected error occurred'];

        $body = <<<JSON
{
    "@context": "\/contexts\/Error",
    "@type": "hydra:Error",
    "hydra:title": "An error occurred",
    "hydra:description": "Status IN PROGRESS is not allowed. Reasons:The following linked SOLs haven't reached the factory: 25470",
    "violations": ["evil"]
}
JSON;
        yield 'Standard error with violations (not implemented yet)' => [$body, 'An unexpected error occurred'];
    }

    public function testMapToStringWilThrow()
    {
        $this->expectException(ClientException::class);

        $client = $this->getClientWithResponse();

        $reponse = $client->request('');
        $reponse->getContent();
    }

    protected function getClientWithResponse(string $body = ''): Client
    {
        $httpClient = new MockHttpClient([
            new MockResponse($body,
                [
                    'response_headers' => ['content-type' => 'application/json'],
                    'http_code' => 400,
                ]),
        ]);

        $securityProphecy = $this->prophesize(Security::class);
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getCacheDir()->shouldBeCalledOnce()->willReturn('');
        $kernel->isDebug()->shouldBeCalledOnce()->willReturn(false);

        return new Client($securityProphecy->reveal(), $eventDispatcherProphecy->reveal(), $kernel->reveal(), $httpClient, [
            'base_uri' => 'https://haproxy:8080',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }
}
