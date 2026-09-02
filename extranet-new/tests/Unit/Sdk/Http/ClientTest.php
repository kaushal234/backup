<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\Http;

use App\Sdk\Http\Client;
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
        );

        $responses = Vec\values($responses);

        self::assertCount(1, $responses);
        self::assertContainsOnlyInstancesOf(ResponseInterface::class, $responses);
        self::assertSame('hello', $responses[0]->getContent());
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
