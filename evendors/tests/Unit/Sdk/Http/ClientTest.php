<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\Http;

use App\Sdk\Http\Client;
use App\Sdk\Http\DistributedHttpSource;
use App\Sdk\Http\HttpSource;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\TestCase;
use Psl\Vec;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

/**
 * @group unit
 */
final class ClientTest extends TestCase
{
    private Client $client;
    private MockHttpClient $mockHttpClient;

    protected function setUp(): void
    {
        $this->mockHttpClient = new MockHttpClient();
        $this->client = new Client($this->mockHttpClient);
    }

    public function testConcurrent(): void
    {
        $this->mockHttpClient->setResponseFactory(static function (string $method, string $uri, array $options) {
            self::assertSame('GET', $method);

            self::assertThat($uri, self::logicalOr(
                new IsIdentical('https://example.com/foo'),
                new IsIdentical('https://example.com/bar'),
            ));

            return new MockResponse($options['response_body'], info: ['http_code' => 200]);
        });

        $responses = $this->client->concurrent(
            HttpSource::create('GET', '/foo', ['response_body' => 'hello']),
            HttpSource::create('GET', '/bar', ['response_body' => 'world']),
        );

        $responses = Vec\values($responses);

        self::assertCount(2, $responses);
        self::assertContainsOnlyInstancesOf(ResponseInterface::class, $responses);
        self::assertSame('hello', $responses[0]->getContent());
        self::assertSame('world', $responses[1]->getContent());
    }

    public function testConcurrentDistribution(): void
    {
        $this->mockHttpClient->setResponseFactory(static function (string $method, string $uri, array $options) {
            self::assertSame('GET', $method);

            self::assertThat($uri, self::logicalOr(
                new IsIdentical('https://example.com/foo'),
                new IsIdentical('https://example.com/bar'),
                new IsIdentical('https://example.com/baz'),
                new IsIdentical('https://example.com/qux'),
            ));

            return new MockResponse($options['response_body'], info: ['http_code' => 200]);
        });

        $responses = $this->client->concurrentDistribution(
            DistributedHttpSource::combine(
                HttpSource::create('GET', '/foo', ['response_body' => 'hello']),
                HttpSource::create('GET', '/bar', ['response_body' => 'world']),
            ),
            DistributedHttpSource::combine(
                HttpSource::create('GET', '/baz', ['response_body' => 'hello']),
                HttpSource::create('GET', '/qux', ['response_body' => 'world']),
            ),
        );

        $responses = Vec\values($responses);

        self::assertCount(2, $responses);
        [$firstDistribution, $secondDistribution] = $responses;

        self::assertCount(2, $firstDistribution);
        self::assertContainsOnlyInstancesOf(ResponseInterface::class, $firstDistribution);
        self::assertSame('hello', $firstDistribution[0]->getContent());
        self::assertSame('world', $firstDistribution[1]->getContent());

        self::assertCount(2, $secondDistribution);
        self::assertContainsOnlyInstancesOf(ResponseInterface::class, $secondDistribution);
        self::assertSame('hello', $secondDistribution[0]->getContent());
        self::assertSame('world', $secondDistribution[1]->getContent());
    }

    public function testForwardedMethods(): void
    {
        $mock = $this->createMock(HttpClientInterface::class);
        $client = new Client($mock);

        $response = new MockResponse('hello');
        $mock->expects($this->once())->method('request')->with('GET', '/', [])->willReturn($response);
        $result = $client->request('GET', '/', []);
        self::assertSame($response, $result);

        $responses = [new MockResponse('hello'), new MockResponse('world')];
        $stream = $this->createMock(ResponseStreamInterface::class);
        $mock->expects($this->once())->method('stream')->with($responses, 4.0)->willReturn($stream);
        $result = $client->stream($responses, 4.0);
        self::assertSame($stream, $result);

        $secondMock = $this->createMock(HttpClientInterface::class);
        $mock->expects($this->once())->method('withOptions')->with(['foo' => 'bar'])->willReturn($secondMock);
        $client = $client->withOptions(['foo' => 'bar']);

        $response = new MockResponse('hello');
        $mock->expects($this->never())->method('request');
        $secondMock->expects($this->once())->method('request')->with('GET', '/', [])->willReturn($response);
        $result = $client->request('GET', '/', []);
        self::assertSame($response, $result);
    }
}
